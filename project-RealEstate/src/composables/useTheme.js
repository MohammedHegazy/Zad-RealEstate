import { ref } from 'vue'

const THEME_KEY = 'zad-theme'

const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')

function getEffectiveTheme(pref) {
  if (pref === 'system') {
    return mediaQuery.matches ? 'dark' : 'light'
  }
  return pref
}

function applyTheme(mode) {
  document.documentElement.setAttribute('data-theme', mode)
  document.documentElement.style.colorScheme = mode
}

const preferred = ref(localStorage.getItem(THEME_KEY) || 'system')
const current = ref(getEffectiveTheme(preferred.value))

applyTheme(current.value)

mediaQuery.addEventListener('change', () => {
  if (preferred.value === 'system') {
    current.value = getEffectiveTheme('system')
    applyTheme(current.value)
  }
})

export function useTheme() {
  function setTheme(mode) {
    preferred.value = mode
    localStorage.setItem(THEME_KEY, mode)
    current.value = getEffectiveTheme(mode)
    applyTheme(current.value)
  }

  function toggle() {
    const next = current.value === 'dark' ? 'light' : 'dark'
    setTheme(next)
  }

  function resetToSystem() {
    setTheme('system')
  }

  return {
    preferred,
    current,
    setTheme,
    toggle,
    resetToSystem,
    isDark: () => current.value === 'dark',
    isLight: () => current.value === 'light',
  }
}
