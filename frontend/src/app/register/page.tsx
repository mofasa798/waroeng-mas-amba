"use client";

import { useAuth } from "@/lib/auth";
import { useState, type FormEvent } from "react";
import Link from "next/link";

export default function RegisterPage() {
  const { login } = useAuth();
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError("");

    if (password !== passwordConfirmation) {
      setError("Password dan konfirmasi password tidak cocok.");
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
      // Auto-login after register
      await login(email, password);
      window.location.href = "/";
    } catch (err: any) {
      const msg = err.response?.data?.message;
      const errors = err.response?.data?.errors;
      if (errors?.email) {
        setError(errors.email[0]);
      } else if (msg) {
        setError(msg);
      } else {
        setError("Registrasi gagal. Coba lagi.");
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
        {error && <p className="bg-red-100 text-red-700 p-3 rounded-xl mb-4 text-center text-lg">{error}</p>}
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
          {loading ? "Loading..." : "Daftar"}
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
