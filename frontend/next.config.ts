import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  output: "standalone",
  allowedDevOrigins: ["waroeng-mas-amba.test"],
};

export default nextConfig;
