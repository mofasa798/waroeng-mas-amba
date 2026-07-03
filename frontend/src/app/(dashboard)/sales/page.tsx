"use client";

import { useEffect, useState } from "react";
import api from "@/lib/api";
import { rupiah } from "@/lib/utils";

type Sale = { id: number; invoice_number: string; total: number; discount: number; grand_total: number; items_count: number; created_at: string; user: { name: string } };

export default function SalesPage() {
  const [sales, setSales] = useState<Sale[]>([]);
  const [loading, setLoading] = useState(true);
  const [date, setDate] = useState("");
  const [invoice, setInvoice] = useState("");
  const [detail, setDetail] = useState<any>(null);

  const fetch = async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      if (date) params.append("date", date);
      const { data } = await api.get(`/sales?${params}`);
      setSales(data.data);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetch(); }, []);

  const lookup = async () => {
    if (!invoice.trim()) return;
    try {
      const { data } = await api.get(`/sales/lookup?invoice=${invoice}`);
      setDetail(data);
    } catch {
      alert("Invoice tidak ditemukan.");
    }
  };

  return (
    <div>
      <h2 className="text-3xl font-bold mb-6">Penjualan</h2>
      <div className="flex flex-wrap gap-3 mb-6">
        <input type="date" value={date} onChange={(e) => setDate(e.target.value)} className="p-3 text-lg border rounded-xl" />
        <button onClick={fetch} className="bg-blue-600 text-white px-6 py-3 rounded-xl text-lg hover:bg-blue-700">Filter</button>
        <input value={invoice} onChange={(e) => setInvoice(e.target.value)} placeholder="Cari invoice..." className="flex-1 p-3 text-lg border rounded-xl min-w-[200px]" />
        <button onClick={lookup} className="bg-gray-600 text-white px-6 py-3 rounded-xl text-lg hover:bg-gray-700">Cari</button>
      </div>

      {detail && (
        <div className="bg-white p-6 rounded-2xl shadow mb-6">
          <h3 className="text-xl font-bold mb-3">Invoice: {detail.invoice_number}</h3>
          <p className="text-lg">Kasir: {detail.user?.name}</p>
          <p className="text-lg">Tanggal: {new Date(detail.created_at).toLocaleString("id")}</p>
          <table className="w-full mt-3 text-left">
            <thead><tr className="border-b text-lg"><th className="p-2">Produk</th><th className="p-2">Qty</th><th className="p-2">Harga</th><th className="p-2">Subtotal</th></tr></thead>
            <tbody>
              {detail.items?.map((i: any) => (
                <tr key={i.id} className="border-b text-lg">
                  <td className="p-2">{i.product?.name}</td>
                  <td className="p-2">{i.quantity}</td>
                  <td className="p-2">{rupiah(i.price)}</td>
                  <td className="p-2">{rupiah(i.price * i.quantity)}</td>
                </tr>
              ))}
            </tbody>
          </table>
          <div className="text-right mt-4 text-lg">
            <p>Total: {rupiah(detail.total)}</p>
            <p>Diskon: {rupiah(detail.discount)}</p>
            <p className="text-xl font-bold">Grand Total: {rupiah(detail.grand_total)}</p>
          </div>
          <button onClick={() => setDetail(null)} className="mt-3 text-blue-600 hover:underline text-lg">Tutup</button>
        </div>
      )}

      {loading ? (
        <p className="text-xl text-gray-500">Loading...</p>
      ) : sales.length === 0 ? (
        <p className="text-xl text-gray-500">Belum ada transaksi.</p>
      ) : (
        <div className="bg-white rounded-2xl shadow overflow-x-auto">
          <table className="w-full text-left">
            <thead>
              <tr className="border-b text-lg">
                <th className="p-4">Invoice</th>
                <th className="p-4">Kasir</th>
                <th className="p-4">Item</th>
                <th className="p-4">Total</th>
                <th className="p-4">Diskon</th>
                <th className="p-4">Grand Total</th>
                <th className="p-4">Tanggal</th>
              </tr>
            </thead>
            <tbody>
              {sales.map((s) => (
                <tr key={s.id} className="border-b hover:bg-gray-50 text-lg">
                  <td className="p-4 font-medium">{s.invoice_number}</td>
                  <td className="p-4">{s.user?.name}</td>
                  <td className="p-4">{s.items_count}</td>
                  <td className="p-4">{rupiah(s.total)}</td>
                  <td className="p-4">{rupiah(s.discount)}</td>
                  <td className="p-4 font-semibold">{rupiah(s.grand_total)}</td>
                  <td className="p-4 text-gray-600">{new Date(s.created_at).toLocaleDateString("id")}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
