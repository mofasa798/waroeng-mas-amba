"use client";

import { useEffect, useState } from "react";
import api from "@/lib/api";
import { rupiah } from "@/lib/utils";

type Product = { id: number; name: string; barcode: string | null; selling_price: number; stock: number; category: { name: string } | null };

export default function ProductsPage() {
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);

  const fetch = async () => {
    setLoading(true);
    try {
      const { data } = await api.get("/products");
      setProducts(data);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetch(); }, []);

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-3xl font-bold">Produk</h2>
        <button className="bg-blue-600 text-white px-6 py-3 rounded-xl text-lg font-semibold hover:bg-blue-700">
          + Tambah
        </button>
      </div>
      {loading ? (
        <p className="text-xl text-gray-500">Loading...</p>
      ) : products.length === 0 ? (
        <p className="text-xl text-gray-500">Belum ada produk.</p>
      ) : (
        <div className="bg-white rounded-2xl shadow overflow-x-auto">
          <table className="w-full text-left">
            <thead>
              <tr className="border-b text-lg">
                <th className="p-4">Nama</th>
                <th className="p-4">Barcode</th>
                <th className="p-4">Harga</th>
                <th className="p-4">Kategori</th>
                <th className="p-4">Stok</th>
              </tr>
            </thead>
            <tbody>
              {products.map((p) => (
                <tr key={p.id} className="border-b hover:bg-gray-50 text-lg">
                  <td className="p-4 font-medium">{p.name}</td>
                  <td className="p-4 text-gray-600">{p.barcode ?? "-"}</td>
                  <td className="p-4">{rupiah(p.selling_price)}</td>
                  <td className="p-4">{p.category?.name ?? "-"}</td>
                  <td className="p-4">
                    <span className={p.stock < 10 ? "text-red-600 font-semibold" : ""}>{p.stock}</span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
