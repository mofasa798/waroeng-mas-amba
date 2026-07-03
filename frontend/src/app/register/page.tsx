"use client";

import { useAuth } from "@/lib/auth";
import { useToast } from "@/components/Toast";
import { useState, type FormEvent } from "react";
import Link from "next/link";

export default function RegisterPage() {
  const { login } = useAuth();
  const { toast } = useToast();
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();

    if (password !== passwordConfirmation) {
      toast("Password dan konfirmasi password tidak cocok.", "error");
      return;
    }

    setLoading(true);
    try {
      const api = (await import("@/lib/api")).default;
      await api.post("/register", {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });
      toast("Registrasi berhasil! Login otomatis...", "success");
      // Auto-login after register
      await login(email, password);
      setTimeout(() => {
        window.location.href = "/";
      }, 800);
    } catch (err: any) {
      if (!err.response) {
        toast("Gagal terhubung ke server. Pastikan backend berjalan.", "error");
      } else {
        const errors = err.response?.data?.errors;
        if (errors?.email) {
          toast(errors.email[0], "error");
        } else if (errors?.password) {
          toast(errors.password[0], "error");
        } else {
          toast(err.response?.data?.message || "Registrasi gagal. Coba lagi.", "error");
        }
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100 p-4">
      <form onSubmit={handleSubmit} className="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
        <h1 className="text-3xl font-bold text-center mb-2">Waroeng Mas Amba</h1>
        <p className="text-center text-gray-500 mb-6 text-lg">Buat akun baru</p>
        <input
          type="text"
          placeholder="Nama lengkap"
          value={name}
          onChange={(e) => setName(e.target.value)}
          required
          className="w-full p-4 text-lg border rounded-xl mb-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
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
          placeholder="Password (min 8 karakter)"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
          minLength={8}
          className="w-full p-4 text-lg border rounded-xl mb-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <input
          type="password"
          placeholder="Konfirmasi password"
          value={passwordConfirmation}
          onChange={(e) => setPasswordConfirmation(e.target.value)}
          required
          minLength={8}
          className="w-full p-4 text-lg border rounded-xl mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <button
          type="submit"
          disabled={loading}
          className="w-full bg-blue-600 text-white p-4 text-lg rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50 mb-4"
        >
          {loading ? "Memproses..." : "Daftar"}
        </button>
        <p className="text-center text-lg">
          Sudah punya akun?{" "}
          <Link href="/login" className="text-blue-600 hover:underline font-semibold">
            Login
          </Link>
        </p>
      </form>
    </div>
  );
}
