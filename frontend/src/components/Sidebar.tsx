"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { cn } from "@/lib/utils";
import { useState } from "react";
import { Package, ShoppingCart, BarChart3, Truck, Users, Menu, X, LayoutDashboard, ClipboardList } from "lucide-react";

const links = [
  { href: "/", label: "Dashboard", icon: LayoutDashboard },
  { href: "/pos", label: "POS (Kasir)", icon: ShoppingCart },
  { href: "/products", label: "Produk", icon: Package },
  { href: "/categories", label: "Kategori", icon: ClipboardList },
  { href: "/suppliers", label: "Supplier", icon: Truck },
  { href: "/sales", label: "Penjualan", icon: BarChart3 },
  { href: "/inventory", label: "Inventaris", icon: Package },
];

export default function Sidebar() {
  const pathname = usePathname();
  const [open, setOpen] = useState(false);

  return (
    <>
      <button
        className="fixed top-4 left-4 z-50 md:hidden bg-white p-2 rounded-xl shadow"
        onClick={() => setOpen(!open)}
      >
        {open ? <X size={28} /> : <Menu size={28} />}
      </button>
      {open && (
        <div className="fixed inset-0 bg-black/30 z-40 md:hidden" onClick={() => setOpen(false)} />
      )}
      <aside
        className={cn(
          "fixed md:static inset-y-0 left-0 z-40 w-64 bg-white border-r flex flex-col transition-transform md:translate-x-0",
          open ? "translate-x-0" : "-translate-x-full"
        )}
      >
        <div className="p-5 border-b">
          <h1 className="text-xl font-bold">Waroeng Mas Amba</h1>
        </div>
        <nav className="flex-1 p-3 space-y-1 overflow-auto">
          {links.map((l) => {
            const Icon = l.icon;
            return (
              <Link
                key={l.href}
                href={l.href}
                onClick={() => setOpen(false)}
                className={cn(
                  "flex items-center gap-3 px-4 py-3 rounded-xl text-lg transition",
                  pathname === l.href ? "bg-blue-100 text-blue-700 font-semibold" : "hover:bg-gray-100"
                )}
              >
                <Icon size={22} />
                {l.label}
              </Link>
            );
          })}
        </nav>
      </aside>
    </>
  );
}
