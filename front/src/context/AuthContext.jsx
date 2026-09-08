import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import { authApi, setToken, getToken, unwrap } from '../api/client'
import { useToast } from './ToastContext'

const AuthContext = createContext(null)

function pickUser(payload) {
  const data = unwrap(payload) ?? payload
  const nested = data?.user ?? data?.data?.user ?? data
  return unwrap(nested) ?? nested
}

function pickToken(payload) {
  const data = unwrap(payload) ?? payload
  return data?.token ?? data?.data?.token ?? null
}

export function AuthProvider({ children }) {
  const toast = useToast()
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  const refresh = useCallback(async () => {
    if (!getToken()) {
      setUser(null)
      setLoading(false)
      return
    }
    try {
      const payload = await authApi.me()
      setUser(pickUser(payload))
    } catch {
      setToken(null)
      setUser(null)
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    refresh()
  }, [refresh])

  const loginWithPayload = useCallback((payload, message) => {
    const token = pickToken(payload)
    if (token) setToken(token)
    setUser(pickUser(payload))
    if (message || payload?.message) toast.success(message || payload.message)
  }, [toast])

  const login = useCallback(async (email, password) => {
    const payload = await authApi.login({ email, password })
    loginWithPayload(payload)
    return payload
  }, [loginWithPayload])

  const register = useCallback(async (body) => {
    const payload = await authApi.register(body)
    toast.success(payload?.message || 'ثبت‌نام با موفقیت انجام شد')
    return payload
  }, [toast])

  const sendOtp = useCallback(async (phone) => {
    const payload = await authApi.sendOtp(phone)
    toast.success(payload?.message || 'کد تایید ارسال شد')
    return payload
  }, [toast])

  const verifyOtp = useCallback(async (phone, code) => {
    const payload = await authApi.verifyOtp({ phone, code })
    loginWithPayload(payload)
    return payload
  }, [loginWithPayload])

  const logout = useCallback(async () => {
    try {
      await authApi.logout()
    } catch {
      /* still clear locally */
    }
    setToken(null)
    setUser(null)
    toast.info('از حساب خارج شدید')
  }, [toast])

  const value = useMemo(
    () => ({ user, loading, login, register, logout, sendOtp, verifyOtp, refresh }),
    [user, loading, login, register, logout, sendOtp, verifyOtp, refresh],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}
