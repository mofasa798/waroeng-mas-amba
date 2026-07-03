"use client";

import { useEffect, useState } from "react";
import api from "@/lib/api";
import { rupiah } from "@/lib/utils";

export default function InventoryPage() {
  const [tab, setTab] = useState<"restock" | "adjust" | "movements" | "insights">("restock");
  const [products, setProducts] = useState<any[]>([]);
  const [productId, setProductId] = useState("");
  const [quantity, setQuantity] = useState("");
  const [note, setNote] = useState("");
  const [movements, setMovements] = useState<any[]>([]);
  const [message, setMessage] = useState("");

  useEffect(() => {
    api.get("/products").then(({ data }) => setProducts(data)).catch(() => {});
  }, []);

  const restock = async () => {
    if (!productId || !quantity) return;
    try {
      const { data } = await api.post(`/products/${productId}/restock`, { quantity: Number(quantity), note });
      setMessage(`✅ Restock berhasil. Stok sekarang: ${data.stock}`);
      setQuantity("");
      setNote("");
    } catch (err: any) {
      setMessage(`❌ ${err.response?.data?.message || "Gagal"}`);
    }
  };

  const adjust = async () => {
    if (!productId || !quantity || !note) return;
    try {
      const { data } = await api.post(`/products/${productId}/adjust-stock`, { quantity: Number(quantity), note });
      setMessage(`✅ Adjustment berhasil. Stok sekarang: ${data.stock}`);
      setQuantity("");
      setNote("");
    } catch (err: any) {
      setMessage(`❌ ${err.response?.data?.message || "Gagal"}`);
    }
  };

  const fetchMovements = async () => {
    try {
      const params = productId ? `?product_id=${productId}` : "";
      const { data } = await api.get(`/stock-movements${params}`);
      setMovements(data.data);
    } catch { /* ignore */ }
  };

  const tabBtn = (key: string, label: string) => (
    <button onClick={() => setTab(key as any)} className={`px-4 py-2 rounded-xl text-lg ${tab === key ? "bg-blue-600 text-white" : "bg-gray-100 hover:bg-gray-200"}`}>
      {label}
    </button>
  );

  const productSelect = (
    <select value={productId} onChange={(e) => setProductId(e.target.value)} className="w-full p-3 text-lg border rounded-xl mb-3">
      <option value="">Pilih produk...</option>
      {products.map((p: any) => <option key={p.id} value={p.id}>{p.name}</option>)}
    </select>
  );

  return (
    <div>
      <h2 className="text-3xl font-bold mb-6">Inventaris</h2>
      <div className="flex gap-2 mb-6 flex-wrap">
        {tabBtn("restock", "Restock")}
        {tabBtn("adjust", "Adjust")}
        {tabBtn("movements", "Riwayat")}
        {tabBtn("insights", "Insights")}
      </div>

      {tab === "restock" && (
        <div className="bg-white p-6 rounded-2xl shadow max-w-md">
          {productSelect}
          <input type="number" min="1" value={quantity} onChange={(e) => setQuantity(e.target.value)} placeholder="Jumlah" className="w-full p-3 text-lg border rounded-xl mb-3" />
          <input value={note} onChange={(e) => setNote(e.target.value)} placeholder="Catatan (opsional)" className="w-full p-3 text-lg border rounded-xl mb-3" />
          <button onClick={restock} className="w-full bg-green-600 text-white py-3 rounded-xl text-lg font-semibold hover:bg-green-700">Restock</button>
          {message && <p className="mt-3 text-center text-lg">{message}</p>}
        </div>
      )}

      {tab === "adjust" && (
        <div className="bg-white p-6 rounded-2xl shadow max-w-md">
          {productSelect}
          <input type="number" value={quantity} onChange={(e) => setQuantity(e.target.value)} placeholder="Jumlah (+/-)" className="w-full p-3 text-lg border rounded-xl mb-3" />
          <input value={note} onChange={(e) => setNote(e.target.value)} placeholder="Alasan (wajib)" className="w-full p-3 text-lg border rounded-xl mb-3" />
          <button onClick={adjust} className="w-full bg-orange-600 text-white py-3 rounded-xl text-lg font-semibold hover:bg-orange-700">Adjust</button>
          {message && <p className="mt-3 text-center text-lg">{message}</p>}
        </div>
      )}

      {tab === "movements" && (
        <div>
          <div className="flex gap-3 mb-4">
            {productSelect}
            <button onClick={fetchMovements} className="bg-blue-600 text-white px-6 py-3 rounded-xl text-lg hover:bg-blue-700">Cari</button>
          </div>
          {movements.length > 0 ? (
            <div className="bg-white rounded-2xl shadow overflow-x-auto">
              <table className="w-full text-left">
                <thead>
                  <tr className="border-b text-lg">
                    <th className="p-4">Produk</th>
                    <th className="p-4">Tipe</th>
                    <th className="p-4">Qty</th>
                    <th className="p-4">Catatan</th>
                    <th className="p-4">User</th>
                    <th className="p-4">Tanggal</th>
                  </tr>
                </thead>
                <tbody>
                  {movements.map((m: any) => (
                    <tr key={m.id} className="border-b text-lg">
                      <td className="p-4">{m.product?.name}</td>
                      <td className="p-4">
                        <span className={`px-2 py-1 rounded-lg text-sm font-semibold ${m.type === "in" ? "bg-green-100 text-green-700" : m.type === "out" ? "bg-red-100 text-red-700" : "bg-yellow-100 text-yellow-700"}`}>
                          {m.type}
                        </span>
                      </td>
                      <td className="p-4">{m.quantity}</td>
                      <td className="p-4 text-gray-600">{m.note ?? "-"}</td>
                      <td className="p-4">{m.user?.name}</td>
                      <td className="p-4 text-gray-600">{new Date(m.created_at).toLocaleString("id")}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <p className="text-gray-500 text-lg">Klik "Cari" untuk melihat riwayat.</p>
          )}
        </div>
      )}

      {tab === "insights" && <Insights />}
    </div>
  );
}

function Insights() {
  const [low, setLow] = useState<any[]>([]);
  const [restock, setRestock] = useState<any[]>([]);
  const [dead, setDead] = useState<any[]>([]);

  useEffect(() => {
    api.get("/inventory/low-stock?threshold=10").then(({ data }) => setLow(data.products)).catch(() => {});
    api.get("/inventory/suggested-restock").then(({ data }) => setRestock(data.products)).catch(() => {});
    api.get("/inventory/dead-stock?days=90").then(({ data }) => setDead(data.products)).catch(() => {});
  }, []);

  return (
    <div className="grid md:grid-cols-3 gap-6">
      <div className="bg-white p-4 rounded-2xl shadow">
        <h3 className="text-xl font-bold mb-3 text-red-600">Stok Menipis</h3>
        {low.length === 0 ? <p className="text-gray-500">Semua aman.</p> : (
          <ul className="space-y-2">
            {low.map((p: any) => <li key={p.id} className="text-lg">{p.name} — <span className="font-semibold">{p.current_stock}</span></li>)}
          </ul>
        )}
      </div>
      <div className="bg-white p-4 rounded-2xl shadow">
        <h3 className="text-xl font-bold mb-3 text-orange-600">Rekomendasi Restock</h3>
        {restock.length === 0 ? <p className="text-gray-500">Tidak ada.</p> : (
          <ul className="space-y-2">
            {restock.map((p: any) => <li key={p.id} className="text-lg">{p.name} — stok: {p.current_stock}, rekomendasi: <span className="font-semibold">{p.suggested_restock_qty}</span></li>)}
          </ul>
        )}
      </div>
      <div className="bg-white p-4 rounded-2xl shadow">
        <h3 className="text-xl font-bold mb-3 text-gray-600">Dead Stock</h3>
        {dead.length === 0 ? <p className="text-gray-500">Tidak ada.</p> : (
          <ul className="space-y-2">
            {dead.map((p: any) => <li key={p.id} className="text-lg">{p.name} — stok: {p.current_stock}, terakhir: {p.last_sold_date ?? "tidak pernah"}</li>)}
          </ul>
        )}
      </div>
    </div>
  );
}
