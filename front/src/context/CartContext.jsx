import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import { cartApi, unwrap } from '../api/client'
import { useAuth } from './AuthContext'
import { useToast } from './ToastContext'

const CartContext = createContext(null)
const CATALOG_CACHE_KEY = 'shop_cart_catalog'

function emptyCart() {
  return {
    lines: [],
    discount_code: null,
    subtotal: 0,
    discount_amount: 0,
    tax: 0,
    total: 0,
    is_empty: true,
  }
}

function readCatalogCache() {
  try {
    return JSON.parse(localStorage.getItem(CATALOG_CACHE_KEY) || '{}')
  } catch {
    return {}
  }
}

function writeCatalogCache(cache) {
  localStorage.setItem(CATALOG_CACHE_KEY, JSON.stringify(cache))
}

export function CartProvider({ children }) {
  const { user } = useAuth()
  const toast = useToast()
  const [cart, setCart] = useState(emptyCart)
  const [catalog, setCatalog] = useState(readCatalogCache)
  const [loading, setLoading] = useState(false)

  const rememberItem = useCallback((item) => {
    if (!item?.item_id && !item?.id) return
    setCatalog((prev) => {
      const next = {
        ...prev,
        [item.item_id]: item,
        [item.id]: item,
      }
      writeCatalogCache(next)
      return next
    })
  }, [])

  const applyCart = useCallback((payload) => {
    const data = unwrap(payload) ?? payload?.data ?? payload
    setCart({ ...emptyCart(), ...(data || {}) })
  }, [])

  const refresh = useCallback(async () => {
    if (!user) {
      setCart(emptyCart())
      return
    }
    setLoading(true)
    try {
      applyCart(await cartApi.get())
    } catch {
      setCart(emptyCart())
    } finally {
      setLoading(false)
    }
  }, [user, applyCart])

  useEffect(() => {
    refresh()
  }, [refresh])

  const addItem = useCallback(async (item, quantity = 1) => {
    const itemId = item.item_id
    if (!itemId) throw new Error('شناسه عددی کالا برای سبد خرید در دسترس نیست')
    rememberItem(item)
    const payload = await cartApi.add({ item_id: itemId, quantity })
    applyCart(payload)
    toast.success(payload?.message || 'کالا به سبد اضافه شد')
    return payload
  }, [applyCart, rememberItem, toast])

  const updateQty = useCallback(async (itemId, quantity) => {
    const payload = await cartApi.update(itemId, quantity)
    applyCart(payload)
    return payload
  }, [applyCart])

  const removeItem = useCallback(async (itemId) => {
    const payload = await cartApi.remove(itemId)
    applyCart(payload)
    toast.success(payload?.message || 'کالا از سبد حذف شد')
    return payload
  }, [applyCart, toast])

  const applyDiscount = useCallback(async (code) => {
    const payload = await cartApi.applyDiscount(code)
    applyCart(payload)
    toast.success(payload?.message || 'کد تخفیف اعمال شد')
    return payload
  }, [applyCart, toast])

  const removeDiscount = useCallback(async () => {
    const payload = await cartApi.removeDiscount()
    applyCart(payload)
    toast.success(payload?.message || 'کد تخفیف حذف شد')
    return payload
  }, [applyCart, toast])

  const count = useMemo(
    () => (cart.lines || []).reduce((sum, line) => sum + Number(line.quantity || 0), 0),
    [cart.lines],
  )

  const value = useMemo(
    () => ({
      cart,
      catalog,
      count,
      loading,
      refresh,
      addItem,
      updateQty,
      removeItem,
      applyDiscount,
      removeDiscount,
      rememberItem,
    }),
    [cart, catalog, count, loading, refresh, addItem, updateQty, removeItem, applyDiscount, removeDiscount, rememberItem],
  )

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>
}

export function useCart() {
  const ctx = useContext(CartContext)
  if (!ctx) throw new Error('useCart must be used within CartProvider')
  return ctx
}
