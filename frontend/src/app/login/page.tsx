"use client";

import { useAuth } from "@/lib/auth";
import { useToast } from "@/components/Toast";
import { useState, type FormEvent } from "react";
import Link from "next/link";

export default function LoginPage() {
  const { login } = useAuth();
  const { toast } = useToast();
  const [email, setEmail] = useState("admin@waroeng.test");
  const [password, setPassword] = useState("password");
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      await login(email, password);
      toast("Login berhasil! Mengalihkan...", "success");
      setTimeout(() => {
        window.location.href = "/";
      }, 800);
    } catch (err: any) {
      if (!err.response) {
        toast("Gagal terhubung ke server. Pastikan backend berjalan.", "error");
      } else if (err.response?.status === 422) {
        toast("Email atau password salah.", "error");
      } else {
        toast("Login gagal. Coba lagi.", "error");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100 p-4">
      <form onSubmit={handleSubmit} className="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
        <h1 className="text-3xl font-bold text-center mb-2">Waroeng Mas Amba</h1>
        <p className="text-center text-gray-500 mb-6 text-lg">Silakan login</p>
        <input
          type="email"
          placeholder="Email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
          className="w-full p-4 text-lg border rounded-xl mb-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <input
          type="password"
          placeholder="Password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
          className="w-full p-4 text-lg border rounded-xl mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <button
          type="submit"
          disabled={loading}
          className="w-full bg-blue-600 text-white p-4 text-lg rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50"
        >
          {loading ? "Memproses..." : "Login"}
        </button>
        <p className="text-center text-lg mt-4">
          Belum punya akun?{" "}
          <Link href="/register" className="text-blue-600 hover:underline font-semibold">
            Daftar
          </Link>
        </p>
      </form>
    </div>
  );
}
