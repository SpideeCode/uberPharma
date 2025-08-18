import { usePage, Link } from "@inertiajs/react";

export default function AdminDashboard() {
  const { stats } = usePage().props;

  return (
    <div className="min-h-screen bg-white text-black">
      <div className="p-6">
        <h1 className="text-3xl font-bold mb-6">Tableau de bord Admin</h1>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <StatCard
            title="Utilisateurs"
            count={stats.users}
            href={route("admin.users")}
          />
          <StatCard
            title="Pharmacies"
            count={stats.pharmacies}
            href={route("admin.pharmacies")}
          />
          <StatCard
            title="Produits"
            count={stats.products}
            href={route("admin.products")}
          />
        </div>
      </div>
    </div>
  );
}

function StatCard({
  title,
  count,
  href,
}: {
  title: string;
  count: number;
  href?: string;
}) {
  if (href) {
    return (
      <Link
        href={href}
        className="block border border-gray-300 rounded-lg p-4 shadow hover:shadow-md transition hover:bg-gray-50"
      >
        <h2 className="text-lg font-semibold mb-1">{title}</h2>
        <p className="text-2xl font-bold">{count}</p>
      </Link>
    );
  }

  return (
    <div className="border border-gray-300 rounded-lg p-4 shadow hover:shadow-md transition hover:bg-gray-50 cursor-pointer">
      <h2 className="text-lg font-semibold mb-1">{title}</h2>
      <p className="text-2xl font-bold">{count}</p>
    </div>
  );
}
