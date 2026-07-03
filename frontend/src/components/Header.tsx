"use client";

import { useAuth } from "@/lib/auth";
import { LogOut, User } from "lucide-react";

export default function Header() {
  const { user, logout } = useAuth();

  return (
    <header className="bg-white border-b px-4 md:px-6 py-3 flex items-center justify-between">
      <div className="flex items-center gap-3">
        <div className="bg-blue-100 p-2 rounded-full">
          <User size={22} className="text-blue-600" />
        </div>
        <div>
          <p className="font-semibold text-lg">{user?.name}</p>
          <p className="text-sm text-gray-500 capitalize">{user?.role}</p>
        </div>
      </div>
      <button
        onClick={logout}
        className="flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 text-lg"
      >
        <LogOut size={20} />
        Logout
      </button>
    </header>
  );
}
