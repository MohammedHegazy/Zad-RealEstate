# =============================================================================
# ml/pricing/server.py — خادم Flask لتوقع سعر العقار (نموذج sklearn)
# =============================================================================
# Laravel لا يشغّل التعلم الآلي داخل PHP؛ يستدعي هذا السيرفر عبر HTTP.
# PricePredictionClient يرسل POST إلى ML_PRICE_PREDICTION_URL/predict (مثلاً http://127.0.0.1:5000/predict)
# المدخلات: JSON بنفس أسماء الحقول التي تدرب عليها النموذج (بما فيها typo num_of_receptioins)
# المخرجات: {"predicted_price": رقم} أو {"error": "..."} مع رمز 500

from flask import Flask, request, jsonify  # Flask = إطار ويب خفيف؛ jsonify = رد JSON
from flask_cors import CORS  # يسمح للمتصفح/لارافيل من منفذ آخر بالاتصال (CORS)
import joblib  # تحميل ملفات .pkl المُدرَّبة مسبقاً
from sklearn.preprocessing import LabelEncoder
import datetime  # لتحويل تاريخ البناء إلى سنة فقط كما في التدريب

# تحميل النموذج والمُرمّز عند بدء السيرفر (مرة واحدة، ليس في كل طلب)
model = joblib.load("real_estate_model.pkl")  # نموذج التنبؤ بالسعر
label_encoder = joblib.load("label_encoder.pkl")  # يحوّل اسم المكان (place) إلى رقم

app = Flask(__name__)
# CORS على /predict فقط — origins "*" للتطوير؛ في الإنتاج يُفضّل تقييد النطاق
CORS(app, resources={r"/predict": {"origins": "*"}})


@app.route("/predict", methods=["POST"])
def predict():
    """
    نقطة التنبؤ الوحيدة التي يستدعيها Laravel.

    ما يدخل: جسم JSON من PricePredictionClient
    ما يحدث: استخراج الميزات → ترميز المكان → predict → إرجاع السعر
    ما يخرج: JSON predicted_price أو error
    """
    try:
        # force=True: يقبل JSON حتى لو لم يُرسل Content-Type بشكل صحيح
        data = request.get_json(force=True)

        # --- استخراج الميزات من الطلب (يجب أن تطابق تدريب النموذج) ---
        place = data["place"]  # نص — سيُحوَّل لاحقاً برقم عبر LabelEncoder
        space_of_estate = float(data["space_of_estate"])
        is_furnished = int(data["is_furnished"])  # 0 أو 1 عادة
        floor = float(data["floor"])
        num_of_bedrooms = float(data["num_of_bedrooms"])
        num_of_livingrooms = float(data["num_of_livingrooms"])
        # مفتاح متعمد بنفس خطأ الإملاء في الواجهة القديمة — لا تغيّره وإلا يفشل الطلب
        num_of_receptions = float(data["num_of_receptioins"])
        num_of_bathrooms = float(data["num_of_bathrooms"])
        num_of_kitchens = float(data["num_of_kitchens"])
        num_of_balconies = float(data["num_of_balconies"])
        date_of_build = data["date_of_build"]  # سلسلة "YYYY-MM-DD"

        # النموذج تدرب على سنة البناء فقط، ليس التاريخ الكامل
        date_of_build = datetime.datetime.strptime(date_of_build, "%Y-%m-%d")
        date_of_build = date_of_build.year

        # transform: المكان يجب أن يكون من القيم التي رآها المُرمّز عند التدريب
        place_encoded = label_encoder.transform([place])[0]

        # ترتيب الميزات ثابت — أي تغيير في الترتيب يُفسد التنبؤ
        features = [
            place_encoded,
            space_of_estate,
            is_furnished,
            floor,
            num_of_bedrooms,
            num_of_livingrooms,
            num_of_receptions,
            num_of_bathrooms,
            num_of_kitchens,
            num_of_balconies,
            date_of_build,
        ]

        # predict يُرجع مصفوفة؛ [0] أول عنصر = السعر المتوقع
        prediction = model.predict([features])

        return jsonify({"predicted_price": prediction[0]})

    except Exception as e:
        # في الإنتاج: سجّل الخطأ ولا تعرض تفاصيل حساسة للعميل
        print(f"Error during prediction: {e}")
        return jsonify({"error": str(e)}), 500


if __name__ == "__main__":
    # تشغيل محلي: python server.py — المنفذ الافتراضي 5000
    app.run(debug=True)
