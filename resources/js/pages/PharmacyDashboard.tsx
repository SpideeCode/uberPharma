import { usePage, Link } from "@inertiajs/react";
import { 
  ShoppingCart, 
  Package, 
  DollarSign, 
  Clock, 
  ArrowUpRight, 
  ArrowDownRight, 
  BarChart2, 
  Truck as TruckIcon, 
  CheckCircle2 as CheckCircleIcon 
} from 'lucide-react';
import Nav from "@/components/Nav";
import { 
  LineChart, 
  Line, 
  CartesianGrid, 
  XAxis, 
  YAxis, 
  Tooltip, 
  ResponsiveContainer 
} from 'recharts';

// --- Types ---
interface Pharmacy {
  id: number;
  name: string;
  address: string;
  city: string;
  status: string;
  status_counts: Record<string, number>;
  revenue: number;
  pending_orders: number;
  in_delivery_orders: number;
  delivered_orders: number;
  cancelled_orders: number;
  products_count: number;
}

interface Order {
  id: number;
  client_name: string;
  client_email: string | null;
  pharmacy_name: string;
  pharmacy_id: number | null;
  total: number;
  total_formatted: string;
  status_value: string;
  status_label: string;
  status_color: string;
  items_count: number;
  created_at_formatted: string;
  created_at_diff: string;
  created_at_raw: string;
  delivery_address: string;
}

interface Product {
  id: number;
  name: string;
  quantity: number;
  revenue: number;
  image?: string;
  total_sold?: number;
  total_revenue?: number;
  order_count?: number;
  pharmacy_name?: string;
}

// --- Custom Tooltip ---
const CustomTooltip = ({ active, payload, label }: any) => {
  if (!active || !payload || !payload.length) return null;
  return (
    <div className="bg-white p-4 border border-gray-200 rounded shadow-md">
      <p className="font-semibold">{label}</p>
      {payload.map((item: any, index: number) => (
        <p key={index} className="text-sm" style={{ color: item.color }}>
          {item.name}: {item.value}
        </p>
      ))}
    </div>
  );
};

// --- StatCard ---
function StatCard({ title, value, icon: Icon, change, changeType = 'up' }: { 
  title: string; 
  value: string | number; 
  icon: any; 
  change?: number; 
  changeType?: 'up' | 'down' 
}) {
  return (
    <div className="bg-white p-6 rounded-lg shadow">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm font-medium text-gray-500">{title}</p>
          <p className="text-2xl font-semibold text-gray-900">{value}</p>
        </div>
        <div className="p-3 rounded-full bg-blue-100 text-blue-600">
          <Icon className="h-6 w-6" />
        </div>
      </div>
      {change !== undefined && (
        <div className={`mt-2 flex items-center text-sm ${changeType === 'up' ? 'text-green-600' : 'text-red-600'}`}>
          {changeType === 'up' ? <ArrowUpRight className="h-4 w-4 mr-1" /> : <ArrowDownRight className="h-4 w-4 mr-1" />}
          <span>{change}% par rapport au mois dernier</span>
        </div>
      )}
    </div>
  );
}

// --- StatusBadge ---
const statusConfig = {
  pending: { bg: 'bg-yellow-500/20', text: 'text-yellow-800', label: 'En attente' },
  accepted: { bg: 'bg-blue-500/20', text: 'text-blue-800', label: 'Acceptée' },
  processing: { bg: 'bg-purple-500/20', text: 'text-purple-800', label: 'En cours' },
  shipped: { bg: 'bg-indigo-500/20', text: 'text-indigo-800', label: 'Expédiée' },
  delivered: { bg: 'bg-emerald-100', text: 'text-emerald-700', label: 'Livrée' },
  cancelled: { bg: 'bg-red-500/20', text: 'text-red-800', label: 'Annulée' },
  refunded: { bg: 'bg-gray-500/20', text: 'text-gray-800', label: 'Remboursée' }
} as const;

type StatusType = keyof typeof statusConfig;

function StatusBadge({ status, label, color }: { status: string; label?: string; color?: string }) {
  const config = statusConfig[status as StatusType] || { bg: 'bg-gray-500/20', text: 'text-gray-800', label: status };
  return (
    <span className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${color || config.bg} ${color ? 'text-white' : config.text}`}>
      {label || config.label}
    </span>
  );
}

// --- Main Component ---
interface DashboardProps {
  pharmacies: Pharmacy[];
  stats: {
    total_pharmacies: number;
    total_orders: number;
    total_products: number;
    total_revenue: number;
    pending_orders: number;
    in_delivery_orders: number;
    delivered_orders: number;
    cancelled_orders: number;
    revenue_by_status: Record<string, number>;
    sales_trend: Array<{ date: string; commandes: number }>;
    revenue_trend: Array<{ date: string; revenu: number }>;
    status_distribution: Array<{
      name: string;
      value: number;
      color: string;
    }>;
    changes?: {
      orders: number;
      revenue: number;
      products: number;
      pending: number;
    };
  };
  recentOrders: Order[];
  topProducts: Product[];
}

export default function PharmacyDashboard({ 
  pharmacies = [], 
  stats = {} as DashboardProps['stats'], 
  recentOrders = [], 
  topProducts = [] 
}: DashboardProps) {
  // Formater les montants
  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('fr-FR', { 
      style: 'currency', 
      currency: 'EUR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(value);
  };
  
  // Préparer les données pour le graphique
  const chartData = stats?.sales_trend?.map(item => ({
    date: item.date,
    commandes: item.commandes || 0
  })) || [];
  
  // Calculer les totaux
  const totalOrders = stats?.total_orders || 0;
  const totalRevenue = stats?.total_revenue || 0;
  const totalProducts = stats?.total_products || 0;

  return (
    <div className="min-h-screen bg-gray-50">
      <Nav />
      <div className="p-4 sm:p-6 max-w-7xl mx-auto">
        {/* Header */}
        <header className="mb-8">
          <h1 className="text-2xl font-bold text-gray-900">Tableau de bord Pharmacie</h1>
          <p className="text-gray-600">Aperçu de vos pharmacies et de leur performance</p>
        </header>

        {/* Stat Cards */}
        <section className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <StatCard 
            title="Chiffre d'affaires" 
            value={formatCurrency(totalRevenue)} 
            icon={DollarSign} 
            change={stats?.changes?.revenue ?? 0}
            changeType={(stats?.changes?.revenue ?? 0) >= 0 ? 'up' : 'down'}
          />
          <StatCard 
            title="Commandes totales" 
            value={totalOrders.toLocaleString('fr-FR')} 
            icon={ShoppingCart} 
            change={stats?.changes?.orders ?? 0}
            changeType={(stats?.changes?.orders ?? 0) >= 0 ? 'up' : 'down'}
          />
          <StatCard 
            title="Produits en stock" 
            value={totalProducts.toLocaleString('fr-FR')} 
            icon={Package} 
            change={stats?.changes?.products ?? 0}
            changeType={(stats?.changes?.products ?? 0) >= 0 ? 'up' : 'down'}
          />
          <StatCard 
            title="Commandes en attente" 
            value={(stats?.pending_orders || 0).toLocaleString('fr-FR')}
            icon={Clock}
            change={stats?.changes?.pending ?? 0}
            changeType={(stats?.changes?.pending ?? 0) >= 0 ? 'up' : 'down'}
          />
        </section>

        {/* Sales Trend Chart */}
        <section className="mb-6">
          <div className="bg-white p-4 rounded-lg shadow">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-medium">Évolution des commandes (30j)</h3>
              <BarChart2 className="h-5 w-5 text-blue-500" />
            </div>
            <div className="h-64 mt-4">
              <ResponsiveContainer width="100%" height="100%">
                <LineChart data={chartData}>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} />
                  <XAxis 
                    dataKey="date" 
                    axisLine={false} 
                    tickLine={false}
                    tick={{ fontSize: 12 }}
                  />
                  <YAxis 
                    axisLine={false} 
                    tickLine={false}
                    tickFormatter={(value: number) => value.toString()}
                    tick={{ fontSize: 12 }}
                  />
                  <Tooltip 
                    content={<CustomTooltip />} 
                    formatter={(value: number) => [value, 'Commandes']}
                    labelFormatter={(label) => `Date: ${label}`}
                  />
                  <Line 
                    type="monotone" 
                    dataKey="commandes" 
                    stroke="#3B82F6" 
                    strokeWidth={2}
                    dot={false}
                    activeDot={{ r: 4 }}
                    name="Commandes"
                  />
                </LineChart>
              </ResponsiveContainer>
            </div>
          </div>
        </section>

        {/* Recent Orders Table */}
        <section className="bg-white rounded-lg shadow overflow-hidden mb-6">
          <div className="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
            <h2 className="text-lg font-medium text-gray-900">Commandes récentes</h2>
            <Link 
              href={route('pharmacy.orders')} 
              className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
            >
              Voir toutes les commandes
            </Link>
          </div>
          <div className="overflow-x-auto">
            {recentOrders.length > 0 ? (
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Commande</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {recentOrders.map(order => (
                    <tr key={order.id} className="hover:bg-gray-50">
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{order.id}</td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm text-gray-900">{order.client_name}</div>
                        <div className="text-sm text-gray-500">{order.client_email}</div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{order.total_formatted}</td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <StatusBadge 
                          status={order.status_value} 
                          label={order.status_label}
                          color={order.status_color}
                        />
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <Link href={route('orders.show', order.id)} className="text-indigo-600 hover:text-indigo-900">
                        Voir détails
                      </Link>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            ) : (
              <div className="text-center py-8 text-gray-500">Aucune commande récente</div>
            )}
          </div>
        </section>

        {/* Produits populaires */}
        <section className="bg-white p-6 rounded-lg shadow mb-6">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-lg font-medium">Produits populaires</h3>
            <Link 
              href={route('products.index', { pharmacy: pharmacies[0]?.id })} 
              className="text-sm text-indigo-600 hover:text-indigo-500"
            >
              Voir tout
            </Link>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {topProducts.slice(0, 3).map((product) => (
              <div key={product.id} className="flex items-center p-3 border rounded-lg hover:bg-gray-50">
                <div className="flex-shrink-0 h-12 w-12 bg-gray-100 rounded-full flex items-center justify-center overflow-hidden">
                  {product.image ? (
                    <img 
                      src={product.image} 
                      alt={product.name} 
                      className="h-full w-full object-cover"
                    />
                  ) : (
                    <Package className="h-5 w-5 text-gray-500" />
                  )}
                </div>
                <div className="ml-4 flex-1">
                  <h4 className="text-sm font-medium text-gray-900 line-clamp-1">{product.name}</h4>
                  <div className="mt-1 flex items-center justify-between">
                    <span className="text-xs text-gray-500">
                      {product.quantity || product.total_sold || 0} ventes
                      {product.pharmacy_name && ` • ${product.pharmacy_name}`}
                    </span>
                    <span className="text-sm font-medium">{formatCurrency(product.revenue || product.total_revenue || 0)}</span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </section>

        {/* Mes Pharmacies */}
        <section className="bg-white rounded-lg shadow overflow-hidden">
          <div className="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
            <h2 className="text-lg font-medium text-gray-900">Mes pharmacies</h2>
            <Link 
              href={route('pharmacies.my')} 
              className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
            >
              Gérer les pharmacies
            </Link>
          </div>
          <ul className="divide-y divide-gray-200">
            {pharmacies.map(pharmacy => (
              <li key={pharmacy.id} className="px-4 py-4 sm:px-6 hover:bg-gray-50">
                <div className="flex items-center justify-between">
                  <div className="flex items-center">
                    <div className="flex-shrink-0 h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden">
                      <Package className="h-5 w-5 text-gray-500" />
                    </div>
                    <div className="ml-4">
                      <div className="text-sm font-medium text-gray-900">{pharmacy.name}</div>
                      <div className="text-sm text-gray-500">{pharmacy.address}, {pharmacy.city}</div>
                    </div>
                  </div>
                  <Link 
                    href={route('pharmacies.show', pharmacy.id)}
                    className="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                  >
                    Voir
                  </Link>
                </div>
              </li>
            ))}
          </ul>
        </section>
      </div>
    </div>
  );
}
