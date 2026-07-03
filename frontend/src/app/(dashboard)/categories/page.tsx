"use client";

import { useEffect, useState } from "react";
import api from "@/lib/api";

type Category = { id: number; name: string };

export default function CategoriesPage() {
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);
  const [name, setName] = useState("");

  const fetch = async () => {
    setLoading(true);
    try {
      const { data } = await api.get("/categories");
      setCategories(data);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetch(); }, []);

  const add = async () => {
    if (!name.trim()) return;
    await api.post("/categories", { name });
    setName("");
    fetch();
  };

  const remove = async (id: number) => {
    if (!confirm("Hapus kategori ini?")) return;
    await api.delete(`/categories/${id}`);
    fetch();
  };

  return (
    <div>
      <h2 className="text-3xl font-bold mb-6">Kategori</h2>
      <div className="flex gap-3 mb-6">
        <input
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="Nama kategori"
          className="flex-1 p-3 text-lg border rounded-xl"
        />
        <button onClick={add} className="bg-blue-600 text-white px-6 py-3 rounded-xl text-lg font-semibold hover:bg-blue-700">
          Tambah
        </button>
      </div>
      {loading ? (
        <p className="text-xl text-gray-500">Loading...</p>
      ) : categories.length === 0 ? (
        <p className="text-xl text-gray-500">Belum ada kategori.</p>
      ) : (
        <div className="bg-white rounded-2xl shadow overflow-x-auto">
          <table className="w-full text-left">
            <thead>
              <tr className="border-b text-lg">
                <th className="p-4">Nama</th>
                <th className="p-4">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {categories.map((c) => (
                <tr key={c.id} className="border-b hover:bg-gray-50 text-lg">
                  <td className="p-4 font-medium">{c.name}</td>
                  <td className="p-4">
                    <button onClick={() => remove(c.id)} className="text-red-600 hover:underline">Hapus</button>
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
