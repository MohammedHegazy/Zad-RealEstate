# ML Price Prediction Service

Standalone Flask microservice used by the Laravel API (`PricePredictionClient`).

## Run locally

```bash
cd ml/pricing
pip install -r requirements.txt
python server.py
```

Default URL: `http://127.0.0.1:5000/predict`

Set `ML_PRICE_PREDICTION_URL` in Laravel `.env` to match.

## Model files

- `Abd_real_estate_model.pkl` — trained regressor
- `label_encoder.pkl` — encodes location labels for the `place` feature

## API contract

POST `/predict` with JSON body (see Laravel `EstatePricePredictionService::buildPayload()`).

Response: `{ "predicted_price": <float> }` or `{ "error": "<message>" }` with HTTP 500.
