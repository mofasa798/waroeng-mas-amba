export default function DashboardPage() {
  return (
    <div>
      <h2 className="text-3xl font-bold mb-6">Dashboard</h2>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        {[
          { label: "Data Produk", desc: "Kelola produk, kategori, & stok", href: "/products" },
          { label: "POS Kasir", desc: "Transaksi penjualan cepat", href: "/pos" },
          { label: "Laporan", desc: "Lihat laporan bisnis", href: "/sales" },
        ].map((card) => (
          <a
            key={card.href}
            href={card.href}
            className="bg-white p-6 rounded-2xl shadow hover:shadow-md transition block"
          >
            <h3 className="text-xl font-semibold mb-2">{card.label}</h3>
            <p className="text-gray-600 text-lg">{card.desc}</p>
          </a>
        ))}
      </div>
    </div>
  );
}
