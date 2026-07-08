"use client";

import { useEffect, useState } from "react";
import api from "@/lib/api";
import { rupiah } from "@/lib/utils";

type Product = { id: number; name: string; barcode: string | null; selling_price: number; cost_price: number; stock: number; category: { id: number; name: string } | null; supplier: { id: number; name: string } | null };
type Category = { id: number; name: string };
type Supplier = { id: number; name: string };

const emptyForm = { name: "", barcode: "", cost_price: 0, selling_price: 0, category_id: "", supplier_id: "", initial_stock: 0 };

export default function ProductsPage() {
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [form, setForm] = useState(emptyForm);
  const [categories, setCategories] = useState<Category[]>([]);
  const [suppliers, setSuppliers] = useState<Supplier[]>([]);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  const fetch = async () => {
    setLoading(true);
    try {
      const { data } = await api.get("/products");
      setProducts(data);
    } finally {
      setLoading(false);
    }
  };

  const openModal = async () => {
    setError("");
    setForm(emptyForm);
    setShowModal(true);
    try {
      const [catRes, supRes] = await Promise.all([api.get("/categories"), api.get("/suppliers")]);
      setCategories(catRes.data);
      setSuppliers(supRes.data);
    } catch {
      // ignore
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setSubmitting(true);
    try {
      const payload = {
        name: form.name,
        barcode: form.barcode || null,
        cost_price: Number(form.cost_price),
        selling_price: Number(form.selling_price),
        category_id: form.category_id ? Number(form.category_id) : null,
        supplier_id: form.supplier_id ? Number(form.supplier_id) : null,
        initial_stock: Number(form.initial_stock),
      };
      await api.post("/products", payload);
      setShowModal(false);
      setForm(emptyForm);
      fetch();
    } catch (err: unknown) {
      const axiosErr = err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
      if (axiosErr.response?.data?.errors) {
        const messages = Object.values(axiosErr.response.data.errors).flat().join(", ");
        setError(messages);
      } else {
        setError(axiosErr.response?.data?.message || "Gagal menambahkan produk");
      }
    } finally {
      setSubmitting(false);
    }
  };

  useEffect(() => { fetch(); }, []);

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-3xl font-bold">Produk</h2>
        <button onClick={openModal} className="bg-blue-600 text-white px-6 py-3 rounded-xl text-lg font-semibold hover:bg-blue-700">
          + Tambah
        </button>
      </div>

      {/* Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onClick={() => setShowModal(false)}>
          <div className="bg-white rounded-2xl shadow-xl p-8 w-full max-w-lg mx-4" onClick={(e) => e.stopPropagation()}>
            <h3 className="text-2xl font-bold mb-6">Tambah Produk</h3>
            {error && (
              <div className="bg-red-50 text-red-700 p-3 rounded-lg mb-4 text-sm">{error}</div>
            )}
            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium mb-1">Nama *</label>
                <input
                  required
                  className="w-full border rounded-lg px-3 py-2 text-lg"
                  value={form.name}
                  onChange={(e) => setForm({ ...form, name: e.target.value })}
                  placeholder="Nama produk"
                />
              </div>
              <div>
                <label className="block text-sm font-medium mb-1">Barcode</label>
                <input
                  className="w-full border rounded-lg px-3 py-2 text-lg"
                  value={form.barcode}
                  onChange={(e) => setForm({ ...form, barcode: e.target.value })}
                  placeholder="(opsional)"
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium mb-1">Harga Beli *</label>
                  <input
                    required
                    type="number"
                    min="0"
                    className="w-full border rounded-lg px-3 py-2 text-lg"
                    value={form.cost_price || ""}
                    onChange={(e) => setForm({ ...form, cost_price: Number(e.target.value) })}
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium mb-1">Harga Jual *</label>
                  <input
                    required
                    type="number"
                    min="0"
                    className="w-full border rounded-lg px-3 py-2 text-lg"
                    value={form.selling_price || ""}
                    onChange={(e) => setForm({ ...form, selling_price: Number(e.target.value) })}
                  />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium mb-1">Kategori</label>
                  <select
                    className="w-full border rounded-lg px-3 py-2 text-lg"
                    value={form.category_id}
                    onChange={(e) => setForm({ ...form, category_id: e.target.value })}
                  >
                    <option value="">-- Pilih --</option>
                    {categories.map((c) => (
                      <option key={c.id} value={c.id}>{c.name}</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium mb-1">Supplier</label>
                  <select
                    className="w-full border rounded-lg px-3 py-2 text-lg"
                    value={form.supplier_id}
                    onChange={(e) => setForm({ ...form, supplier_id: e.target.value })}
                  >
                    <option value="">-- Pilih --</option>
                    {suppliers.map((s) => (
                      <option key={s.id} value={s.id}>{s.name}</option>
                    ))}
                  </select>
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium mb-1">Stok Awal</label>
                <input
                  type="number"
                  min="0"
                  className="w-full border rounded-lg px-3 py-2 text-lg"
                  value={form.initial_stock || ""}
                  onChange={(e) => setForm({ ...form, initial_stock: Number(e.target.value) })}
                  placeholder="0"
                />
              </div>
              <div className="flex gap-3 pt-4">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="flex-1 border border-gray-300 rounded-lg py-3 text-lg font-medium hover:bg-gray-50"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={submitting}
                  className="flex-1 bg-blue-600 text-white rounded-lg py-3 text-lg font-semibold hover:bg-blue-700 disabled:opacity-50"
                >
                  {submitting ? "Menyimpan..." : "Simpan"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
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
