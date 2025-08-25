import { usePage, Link } from "@inertiajs/react";
import { ShoppingCart, Package, DollarSign, Clock, TrendingUp, ArrowUpRight, ArrowDownRight } from 'lucide-react';
import Nav from "@/components/Nav";

// Composant de carte de statistique
function StatCard({ title, value, icon: Icon, change, changeType = 'up' }: { title: string; value: string | number; icon: any; change?: number; changeType?: 'up' | 'down' }) {
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
          {changeType === 'up' ? (
            <ArrowUpRight className="h-4 w-4 mr-1" />
          ) : (
            <ArrowDownRight className="h-4 w-4 mr-1" />
          )}
          <span>{change}% par rapport au mois dernier</span>
        </div>
      )}
    </div>
  );
}

// Composant de badge de statut
function StatusBadge({ status }: { status: string }) {
  const statusConfig = {
    pending: {
      bg: 'bg-yellow-100',
      text: 'text-yellow-800',
      label: 'En attente'
    },
    processing: {
      bg: 'bg-blue-100',
      text: 'text-blue-800',
      label: 'En cours'
    },
    completed: {
      bg: 'bg-green-100',
      text: 'text-green-800',
      label: 'Terminée'
    },
    cancelled: {
      bg: 'bg-red-100',
      text: 'text-red-800',
      label: 'Annulée'
    }
  };

  const config = statusConfig[status as keyof typeof statusConfig] || statusConfig.pending;
  
  return (
    <span className={`px-2 py-1 text-xs font-medium rounded-full ${config.bg} ${config.text}`}>
      {config.label}
    </span>
  );
}

export default function PharmacyDashboard({ pharmacies, stats, recentOrders, topProducts }: any) {
  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Nav />
      <div className="p-6 max-w-7xl mx-auto">
        <div className="flex justify-between items-center mb-8">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Tableau de bord Pharmacie</h1>
            <p className="text-gray-600">Aperçu de vos pharmacies et de leur performance</p>
          </div>
        </div>

        {/* Cartes de statistiques */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <StatCard 
            title="Total des ventes" 
            value={formatCurrency(stats.total_revenue || 0)} 
            icon={DollarSign} 
            change={12.5} 
          />
          <StatCard 
            title="Commandes" 
            value={stats.total_orders || 0} 
            icon={ShoppingCart} 
            change={8.3} 
          />
          <StatCard 
            title="Produits en stock" 
            value={stats.total_products || 0} 
            icon={Package} 
            change={5.2} 
          />
          <StatCard 
            title="Commandes en attente" 
            value={stats.pending_orders || 0} 
            icon={Clock} 
            change={-2.1} 
            changeType="down"
          />
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          {/* Liste des pharmacies */}
          <div className="lg:col-span-1">
            <div className="bg-white p-6 rounded-lg shadow">
              <h2 className="text-lg font-medium text-gray-900 mb-4">Vos pharmacies</h2>
              {pharmacies.length === 0 ? (
                <p className="text-gray-500">Vous n'avez pas encore de pharmacies.</p>
              ) : (
                <ul className="space-y-4">
                  {pharmacies.map((pharmacy: any) => (
                    <li key={pharmacy.id} className="border-b pb-4 last:border-b-0 last:pb-0">
                      <div className="flex justify-between items-start">
                        <div>
                          <h3 className="font-medium text-gray-900">{pharmacy.name}</h3>
                          <p className="text-sm text-gray-500">{pharmacy.orders_count} commandes</p>
                          <p className="text-sm font-medium text-gray-900 mt-1">
                            {formatCurrency(pharmacy.revenue || 0)}
                          </p>
                        </div>
                        <div className="flex space-x-2">
                          <Link
                            href={`/pharmacies/${pharmacy.id}/edit`}
                            className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                          >
                            Voir
                          </Link>
                        </div>
                      </div>
                    </li>
                  ))}
                </ul>
              )}
            </div>
          </div>

          {/* Commandes récentes */}
          <div className="lg:col-span-2">
            <div className="bg-white p-6 rounded-lg shadow">
              <div className="flex justify-between items-center mb-4">
                <h2 className="text-lg font-medium text-gray-900">Commandes récentes</h2>
                <Link href="/orders" className="text-sm text-blue-600 hover:text-blue-800">
                  Voir tout
                </Link>
              </div>
              
              {recentOrders.length > 0 ? (
                <div className="overflow-x-auto">
                  <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          N° Commande
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Client
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Montant
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Statut
                        </th>
                      </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                      {recentOrders.map((order: any) => (
                        <tr key={order.id} className="hover:bg-gray-50">
                          <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #{order.id}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {order.client}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {formatCurrency(order.total)}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <StatusBadge status={order.status} />
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              ) : (
                <p className="text-gray-500">Aucune commande récente.</p>
              )}
            </div>

            {/* Produits populaires */}
            <div className="mt-6 bg-white p-6 rounded-lg shadow">
              <h2 className="text-lg font-medium text-gray-900 mb-4">Produits populaires</h2>
              
              {topProducts.length > 0 ? (
                <div className="space-y-4">
                  {topProducts.map((product: any) => (
                    <div key={product.id} className="flex items-center justify-between">
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium text-gray-900 truncate">{product.name}</p>
                        <p className="text-sm text-gray-500">{product.sales || 0} ventes</p>
                      </div>
                      <div className="ml-4 text-right">
                        <p className="text-sm font-medium text-gray-900">
                          {formatCurrency(product.revenue || 0)}
                        </p>
                        <p className="text-xs text-gray-500">
                          {product.revenue > 0 ? (
                            <span className="text-green-600">+{Math.round((product.revenue / stats.total_revenue) * 100)}%</span>
                          ) : '0%'}
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-gray-500">Aucun produit vendu pour le moment.</p>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
