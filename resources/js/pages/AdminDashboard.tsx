import { usePage, Link } from "@inertiajs/react";
import { LineChart, Line, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { ArrowUp, ArrowDown, ShoppingCart, Users, Package, DollarSign } from 'lucide-react';
import Nav from '../components/Nav';
import { type SharedData } from "@/types";

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884d8'];

interface StatCardProps {
  title: string;
  value: number | string;
  change?: number;
  icon: React.ReactNode;
  href?: string;
}

interface Order {
  id: number;
  client: string;
  pharmacy: string;
  total: number;
  status: string;
  date: string;
}

export default function AdminDashboard() {
  const { stats, recentOrders, topPharmacies, orderStats, revenueStats, topProducts } = usePage<{
    stats: any;
    recentOrders: Order[];
    topPharmacies: any[];
    orderStats: any[];
    revenueStats: any[];
    topProducts: any[];
  }>().props;

  const { auth } = usePage<SharedData>().props;

  const renderStatusBadge = (status: string) => {
    const statusClasses = {
      completed: 'bg-green-100 text-green-800',
      pending: 'bg-yellow-100 text-yellow-800',
      cancelled: 'bg-red-100 text-red-800',
      processing: 'bg-blue-100 text-blue-800',
    };

    return (
      <span className={`px-2 py-1 text-xs font-medium rounded-full ${statusClasses[status as keyof typeof statusClasses] || 'bg-gray-100 text-gray-800'}`}>
        {status}
      </span>
    );
  };

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
  };

  return (
    <div className="min-h-screen bg-gray-50 text-gray-900">
      <Nav />
      <div className="p-6 max-w-7xl mx-auto">
        <div className="flex justify-between items-center mb-8">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Tableau de bord Admin</h1>
            <p className="text-gray-600">Aperçu des performances et statistiques</p>
          </div>
          <div className="text-sm text-gray-500">
            {new Date().toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
          </div>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <StatCard 
            title="Utilisateurs" 
            value={stats.users.total} 
            change={stats.users.growth}
            icon={<Users className="h-6 w-6 text-blue-500" />}
            href={route("admin.users")}
          />
          <StatCard 
            title="Pharmacies" 
            value={stats.pharmacies.total} 
            change={stats.pharmacies.growth}
            icon={<Package className="h-6 w-6 text-green-500" />}
            href={route("admin.pharmacies")}
          />
          <StatCard 
            title="Commandes" 
            value={stats.orders.total} 
            change={stats.orders.growth}
            icon={<ShoppingCart className="h-6 w-6 text-yellow-500" />}
          />
          <StatCard 
            title="Revenus (30j)" 
            value={formatCurrency(stats.orders.revenue_this_month)} 
            change={Math.round((stats.orders.revenue_this_month / (stats.orders.revenue - stats.orders.revenue_this_month + 1)) * 100)}
            icon={<DollarSign className="h-6 w-6 text-purple-500" />}
          />
        </div>

        {/* Charts Row */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          {/* Orders Chart */}
          <div className="bg-white p-6 rounded-xl shadow">
            <h3 className="text-lg font-medium mb-4">Commandes des 6 derniers mois</h3>
            <div className="h-80">
              <ResponsiveContainer width="100%" height="100%">
                <LineChart data={orderStats}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                  <XAxis dataKey="month" />
                  <YAxis />
                  <Tooltip />
                  <Legend />
                  <Line type="monotone" dataKey="orders" stroke="#3B82F6" strokeWidth={2} name="Commandes" />
                </LineChart>
              </ResponsiveContainer>
            </div>
          </div>

          {/* Revenue Chart */}
          <div className="bg-white p-6 rounded-xl shadow">
            <h3 className="text-lg font-medium mb-4">Revenus des 6 derniers mois</h3>
            <div className="h-80">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={revenueStats}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                  <XAxis dataKey="month" />
                  <YAxis />
                  <Tooltip formatter={(value: any) => [formatCurrency(Number(value)), 'Revenu']} />
                  <Legend />
                  <Bar dataKey="revenue" fill="#10B981" name="Revenu" radius={[4, 4, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </div>
        </div>

        {/* Bottom Row */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Recent Orders */}
          <div className="lg:col-span-2 bg-white p-6 rounded-xl shadow">
            <div className="flex justify-between items-center mb-4">
              <h3 className="text-lg font-medium">Commandes récentes</h3>
              <Link href={route('admin.orders')} className="text-sm text-blue-600 hover:underline">
                Voir tout
              </Link>
            </div>
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pharmacie</th>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {recentOrders.map((order) => (
                    <tr key={order.id} className="hover:bg-gray-50">
                      <td className="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">#{order.id}</td>
                      <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{order.client}</td>
                      <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{order.pharmacy}</td>
                      <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{formatCurrency(order.total)}</td>
                      <td className="px-4 py-3 whitespace-nowrap">
                        {renderStatusBadge(order.status)}
                      </td>
                      <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {new Date(order.date).toLocaleString('fr-FR')}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

          {/* Top Pharmacies & Products */}
          <div className="space-y-6">
            {/* Top Pharmacies */}
            <div className="bg-white p-6 rounded-xl shadow">
              <h3 className="text-lg font-medium mb-4">Meilleures pharmacies</h3>
              <div className="space-y-4">
                {topPharmacies.map((pharmacy, index) => (
                  <div key={pharmacy.id} className="flex items-center justify-between">
                    <div className="flex items-center space-x-3">
                      <span className="text-sm font-medium text-gray-500">{index + 1}.</span>
                      <div>
                        <p className="text-sm font-medium text-gray-900">{pharmacy.name}</p>
                        <p className="text-xs text-gray-500">{pharmacy.orders_count} commandes</p>
                      </div>
                    </div>
                    <span className="text-sm font-medium text-gray-900">
                      {formatCurrency(pharmacy.total_revenue)}
                    </span>
                  </div>
                ))}
              </div>
            </div>

            {/* Top Products */}
            <div className="bg-white p-6 rounded-xl shadow">
              <h3 className="text-lg font-medium mb-4">Produits populaires</h3>
              <div className="space-y-4">
                {topProducts.map((product, index) => (
                  <div key={product.id} className="flex items-center justify-between">
                    <div className="flex items-center space-x-3">
                      <span className="text-sm font-medium text-gray-500">{index + 1}.</span>
                      <div>
                        <p className="text-sm font-medium text-gray-900">{product.name}</p>
                        <p className="text-xs text-gray-500">{product.pharmacy}</p>
                      </div>
                    </div>
                    <div className="text-right">
                      <p className="text-sm font-medium text-gray-900">{product.sales} ventes</p>
                      <p className="text-xs text-gray-500">{formatCurrency(product.revenue)}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function StatCard({ title, value, change, icon, href }: StatCardProps) {
  const content = (
    <div className="bg-white p-6 rounded-xl shadow flex items-center">
      <div className="p-3 rounded-lg bg-opacity-10 bg-gray-200 mr-4">
        {icon}
      </div>
      <div>
        <p className="text-sm font-medium text-gray-500">{title}</p>
        <div className="flex items-center">
          <p className="text-2xl font-bold text-gray-900">{value}</p>
          {change !== undefined && (
            <span className={`ml-2 flex items-center text-sm font-medium ${change >= 0 ? 'text-green-600' : 'text-red-600'}`}>
              {change >= 0 ? (
                <ArrowUp className="h-4 w-4 mr-1" />
              ) : (
                <ArrowDown className="h-4 w-4 mr-1" />
              )}
              {Math.abs(change)}%
            </span>
          )}
        </div>
      </div>
    </div>
  );

  if (href) {
    return <Link href={href}>{content}</Link>;
  }

  return content;
}
