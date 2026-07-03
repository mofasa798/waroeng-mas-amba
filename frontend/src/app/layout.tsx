import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "Waroeng Mas Amba",
  description: "POS & Inventory System",
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="id">
      <body>{children}</body>
    </html>
  );
}
