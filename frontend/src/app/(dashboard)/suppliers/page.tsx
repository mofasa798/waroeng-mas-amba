"use client";

import { useEffect, useState } from "react";
import api from "@/lib/api";

type Supplier = { id: number; name: string; phone: string | null; address: string | null };

export default function SuppliersPage() {
  const [suppliers, setSuppliers] = useState<Supplier[]>([]);
  const [loading, setLoading] = useState(true);
  const [form, setForm] = useState({ name: "", phone: "", address: "" });

  const fetch = async () => {
    setLoading(true);
    try {
      const { data } = await api.get("/suppliers");
      setSuppliers(data);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetch(); }, []);

  const add = async () => {
    if (!form.name.trim()) return;
    await api.post("/suppliers", form);
    setForm({ name: "", phone: "", address: "" });
    fetch();
  };

  const remove = async (id: number) => {
    if (!confirm("Hapus supplier ini?")) return;
    await api.delete(`/suppliers/${id}`);
    fetch();
  };

  return (
    <div>
      <h2 className="text-3xl font-bold mb-6">Supplier</h2>
      <div className="bg-white p-4 rounded-2xl shadow mb-6 flex flex-wrap gap-3">
        <input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} placeholder="Nama supplier" className="flex-1 p-3 text-lg border rounded-xl min-w-[200px]" />
        <input value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} placeholder="Telepon" className="flex-1 p-3 text-lg border rounded-xl min-w-[150px]" />
        <input value={form.address} onChange={(e) => setForm({ ...form, address: e.target.value })} placeholder="Alamat" className="flex-1 p-3 text-lg border rounded-xl min-w-[200px]" />
        <button onClick={add} className="bg-blue-600 text-white px-6 py-3 rounded-xl text-lg font-semibold hover:bg-blue-700">Tambah</button>
      </div>
      {loading ? (
        <p className="text-xl text-gray-500">Loading...</p>
      ) : suppliers.length === 0 ? (
        <p className="text-xl text-gray-500">Belum ada supplier.</p>
      ) : (
        <div className="bg-white rounded-2xl shadow overflow-x-auto">
          <table className="w-full text-left">
            <thead>
              <tr className="border-b text-lg">
                <th className="p-4">Nama</th>
                <th className="p-4">Telepon</th>
                <th className="p-4">Alamat</th>
                <th className="p-4">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {suppliers.map((s) => (
                <tr key={s.id} className="border-b hover:bg-gray-50 text-lg">
                  <td className="p-4 font-medium">{s.name}</td>
                  <td className="p-4 text-gray-600">{s.phone ?? "-"}</td>
                  <td className="p-4 text-gray-600">{s.address ?? "-"}</td>
                  <td className="p-4">
                    <button onClick={() => remove(s.id)} className="text-red-600 hover:underline">Hapus</button>
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
