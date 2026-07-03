"use client";

import { useEffect, useRef, useState } from "react";
import api from "@/lib/api";
import { rupiah } from "@/lib/utils";

type Product = { id: number; name: string; barcode: string | null; selling_price: number; stock: number };

export default function PosPage() {
  const [query, setQuery] = useState("");
  const [results, setResults] = useState<Product[]>([]);
  const [cart, setCart] = useState<{ product: Product; qty: number }[]>([]);
  const [discount, setDiscount] = useState(0);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState("");
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    inputRef.current?.focus();
  }, []);

  useEffect(() => {
    if (query.length < 2) { setResults([]); return; }
    const timer = setTimeout(async () => {
      try {
        const { data } = await api.get(`/products/search?q=${query}`);
        setResults(data);
      } catch { /* ignore */ }
    }, 200);
    return () => clearTimeout(timer);
  }, [query]);

  const addToCart = (p: Product) => {
    setCart((prev) => {
      const existing = prev.find((c) => c.product.id === p.id);
      if (existing) {
        if (existing.qty >= p.stock) return prev;
        return prev.map((c) => (c.product.id === p.id ? { ...c, qty: c.qty + 1 } : c));
      }
      return [...prev, { product: p, qty: 1 }];
    });
    setQuery("");
    setResults([]);
    inputRef.current?.focus();
  };

  const updateQty = (id: number, qty: number) => {
    setCart((prev) =>
      qty <= 0
        ? prev.filter((c) => c.product.id !== id)
        : prev.map((c) => (c.product.id === id ? { ...c, qty: Math.min(qty, c.product.stock) } : c))
    );
  };

  const total = cart.reduce((sum, c) => sum + c.product.selling_price * c.qty, 0);
  const grandTotal = Math.max(0, total - discount);

  const checkout = async () => {
    if (cart.length === 0) return;
    setLoading(true);
    setMessage("");
    try {
      const { data } = await api.post("/checkout", {
        items: cart.map((c) => ({ product_id: c.product.id, quantity: c.qty })),
        discount,
      });
      setMessage(`✅ Invoice: ${data.invoice_number} — Rp ${data.grand_total.toLocaleString()}`);
      setCart([]);
      setDiscount(0);
    } catch (err: any) {
      setMessage(`❌ ${err.response?.data?.message || err.message}`);
    } finally {
      setLoading(false);
      inputRef.current?.focus();
    }
  };

  return (
    <div className="flex flex-col md:flex-row gap-6 h-full">
      {/* Left: Search & Products */}
      <div className="flex-1 flex flex-col">
        <h2 className="text-3xl font-bold mb-4">POS Kasir</h2>
        <input
          ref={inputRef}
          autoFocus
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder="Cari produk... (scan barcode / ketik nama)"
          className="w-full p-4 text-lg border rounded-xl mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        {results.length > 0 && (
          <div className="bg-white rounded-2xl shadow overflow-y-auto max-h-60 mb-4">
            {results.map((p) => (
              <button
                key={p.id}
                onClick={() => addToCart(p)}
                className="w-full text-left px-4 py-3 border-b hover:bg-blue-50 flex justify-between items-center text-lg"
              >
                <span>
                  {p.name}{" "}
                  {p.barcode && <span className="text-gray-400 text-sm">({p.barcode})</span>}
                </span>
                <span className="font-semibold">{rupiah(p.selling_price)}</span>
              </button>
            ))}
          </div>
        )}
        {/* Quick products */}
        <div className="grid grid-cols-2 md:grid-cols-3 gap-3 overflow-y-auto flex-1">
          {results.length === 0 &&
            [].length === 0 &&
            query.length < 2 &&
            null}
        </div>
      </div>

      {/* Right: Cart */}
      <div className="w-full md:w-96 bg-white rounded-2xl shadow flex flex-col">
        <div className="p-4 border-b">
          <h3 className="text-xl font-bold">Keranjang ({cart.length})</h3>
        </div>
        <div className="flex-1 overflow-auto p-4 space-y-3">
          {cart.length === 0 && <p className="text-gray-500 text-lg text-center mt-10">Belum ada item.</p>}
          {cart.map((c) => (
            <div key={c.product.id} className="flex items-center justify-between border-b pb-3">
              <div>
                <p className="font-semibold text-lg">{c.product.name}</p>
                <p className="text-sm text-gray-500">{rupiah(c.product.selling_price)}</p>
              </div>
              <div className="flex items-center gap-2">
                <button onClick={() => updateQty(c.product.id, c.qty - 1)} className="w-10 h-10 rounded-xl bg-gray-100 text-xl">−</button>
                <span className="w-8 text-center text-xl font-semibold">{c.qty}</span>
                <button
                  onClick={() => updateQty(c.product.id, c.qty + 1)}
                  disabled={c.qty >= c.product.stock}
                  className="w-10 h-10 rounded-xl bg-gray-100 text-xl disabled:opacity-30"
                >+</button>
              </div>
            </div>
          ))}
        </div>
        <div className="border-t p-4 space-y-3">
          <div className="flex justify-between text-lg">
            <span>Subtotal</span>
            <span>{rupiah(total)}</span>
          </div>
          <div className="flex gap-2 items-center">
            <span className="text-lg">Diskon</span>
            <input
              type="number"
              value={discount}
              onChange={(e) => setDiscount(Math.max(0, Number(e.target.value)))}
              className="w-28 p-2 border rounded-xl text-lg text-right"
            />
          </div>
          <div className="flex justify-between text-xl font-bold">
            <span>Total</span>
            <span>{rupiah(grandTotal)}</span>
          </div>
          <button
            onClick={checkout}
            disabled={cart.length === 0 || loading}
            className="w-full bg-green-600 text-white py-4 rounded-xl text-xl font-bold hover:bg-green-700 disabled:opacity-50"
          >
            {loading ? "Processing..." : "Bayar (F1)"}
          </button>
          {message && (
            <p className={`text-center text-lg ${message.startsWith("✅") ? "text-green-600" : "text-red-600"}`}>
              {message}
            </p>
          )}
        </div>
      </div>
    </div>
  );
}
