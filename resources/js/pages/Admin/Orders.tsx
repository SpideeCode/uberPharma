import { usePage, Link } from "@inertiajs/react";
import { ArrowUp, ArrowDown, CheckCircle, Clock, XCircle, Loader2 } from 'lucide-react';
import Nav from '../../components/Nav';

interface Order {
  id: number;
  client: {
    name: string;
    email: string;
  } | null;
  pharmacy: {
    name: string;
  } | null;
  total_price: number;
  status: string;
  created_at: string;
}

interface PaginatedOrders {
  data: Order[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
  prev_page_url: string | null;
  next_page_url: string | null;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

// Extension de l'interface de base de PageProps de Inertia
import { PageProps as InertiaPageProps } from '@inertiajs/core';

interface PageProps extends InertiaPageProps {
  orders: PaginatedOrders;
  auth: {
    user: {
      id: number;
      name: string;
      email: string;
      role: string;
    };
  };
  ziggy: {
    location: string;
    query: Record<string, any>;
    url: string;
  };
  [key: string]: any; // Pour les propriétés supplémentaires
}

// Composant de pagination simple
function Pagination({ currentPage, lastPage, onPageChange }: { 
  currentPage: number; 
  lastPage: number; 
  onPageChange: (page: number) => void 
}) {
  return (
    <div className="flex items-center justify-between px-6 py-3 bg-gray-50 border-t border-gray-200 sm:px-6">
      <div className="flex-1 flex justify-between sm:hidden">
        <button
          onClick={() => onPageChange(currentPage - 1)}
          disabled={currentPage === 1}
          className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
        >
          Précédent
        </button>
        <button
          onClick={() => onPageChange(currentPage + 1)}
          disabled={currentPage === lastPage}
          className="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
        >
          Suivant
        </button>
      </div>
      <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
          <p className="text-sm text-gray-700">
            Affichage de <span className="font-medium">{(currentPage - 1) * 20 + 1}</span> à{' '}
            <span className="font-medium">
              {Math.min(currentPage * 20, 20 * lastPage)}
            </span>{' '}
            sur <span className="font-medium">{20 * lastPage}</span> résultats
          </p>
        </div>
        <div>
          <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <button
              onClick={() => onPageChange(currentPage - 1)}
              disabled={currentPage === 1}
              className="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
            >
              <span className="sr-only">Précédent</span>
              <ArrowUp className="h-5 w-5 transform -rotate-90" aria-hidden="true" />
            </button>
            <button
              onClick={() => onPageChange(currentPage + 1)}
              disabled={currentPage === lastPage}
              className="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
            >
              <span className="sr-only">Suivant</span>
              <ArrowDown className="h-5 w-5 transform -rotate-90" aria-hidden="true" />
            </button>
          </nav>
        </div>
      </div>
    </div>
  );
}

export default function AdminOrders() {
  const { orders } = usePage<PageProps>().props;

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
  };

  const getStatusBadge = (status: string) => {
    const statusConfig = {
      pending: {
        icon: <Clock className="h-4 w-4" />,
        bg: 'bg-yellow-100',
        text: 'text-yellow-800',
        label: 'En attente'
      },
      processing: {
        icon: <Loader2 className="h-4 w-4 animate-spin" />,
        bg: 'bg-blue-100',
        text: 'text-blue-800',
        label: 'En cours'
      },
      completed: {
        icon: <CheckCircle className="h-4 w-4" />,
        bg: 'bg-green-100',
        text: 'text-green-800',
        label: 'Terminée'
      },
      cancelled: {
        icon: <XCircle className="h-4 w-4" />,
        bg: 'bg-red-100',
        text: 'text-red-800',
        label: 'Annulée'
      }
    };

    const config = statusConfig[status as keyof typeof statusConfig] || statusConfig.pending;
    
    return (
      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${config.bg} ${config.text}`}>
        {config.icon}
        <span className="ml-1">{config.label}</span>
      </span>
    );
  };

  return (
    <div className="min-h-screen bg-gray-50 text-gray-900">
      <Nav />
      <div className="p-6 max-w-7xl mx-auto">
        <div className="flex justify-between items-center mb-6">
          <div>
            <h1 className="text-2xl font-bold">Gestion des commandes</h1>
            <p className="text-gray-600">Liste complète des commandes passées sur la plateforme</p>
          </div>
        </div>

        <div className="bg-white shadow overflow-hidden sm:rounded-lg">
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    N° Commande
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Client
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Pharmacie
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Montant
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Statut
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                  </th>
                  <th scope="col" className="relative px-6 py-3">
                    <span className="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {orders.data.map((order) => (
                  <tr key={order.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      #{order.id}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <div className="flex items-center">
                        <div className="ml-4">
                          <div className="text-sm font-medium text-gray-900">
                            {order.client?.name || 'Client inconnu'}
                          </div>
                          <div className="text-sm text-gray-500">
                            {order.client?.email || ''}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {order.pharmacy?.name || 'Pharmacie inconnue'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {formatCurrency(order.total_price)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {getStatusBadge(order.status)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {new Date(order.created_at).toLocaleDateString('fr-FR', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                      })}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <Link 
                        href={`/orders/${order.id}`}
                        className="text-indigo-600 hover:text-indigo-900"
                      >
                        Voir
                      </Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          
          {/* Pagination */}
          {orders.last_page > 1 && (
            <div className="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
              <div className="flex-1 flex justify-between sm:hidden">
                <Link 
                  href={orders.prev_page_url || '#'} 
                  className={`relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md ${!orders.prev_page_url ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-50'}`}
                  onClick={(e) => !orders.prev_page_url && e.preventDefault()}
                >
                  Précédent
                </Link>
                <Link 
                  href={orders.next_page_url || '#'} 
                  className={`ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md ${!orders.next_page_url ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-50'}`}
                  onClick={(e) => !orders.next_page_url && e.preventDefault()}
                >
                  Suivant
                </Link>
              </div>
              <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p className="text-sm text-gray-700">
                    Affichage de <span className="font-medium">{orders.from}</span> à <span className="font-medium">{orders.to}</span> sur{' '}
                    <span className="font-medium">{orders.total}</span> résultat{orders.total > 1 ? 's' : ''}
                  </p>
                </div>
                <div>
                  <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    {orders.links.map((link, index) => {
                      // Ne pas afficher les liens vides
                      if (!link.url) {
                        return (
                          <span 
                            key={index}
                            className="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300"
                            dangerouslySetInnerHTML={{ __html: link.label }}
                          />
                        );
                      }

                      // Récupérer le numéro de page depuis l'URL
                      const pageMatch = link.url.match(/page=(\d+)/);
                      const page = pageMatch ? parseInt(pageMatch[1]) : 1;
                      
                      return (
                        <Link
                          key={index}
                          href={link.url}
                          className={`relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
                            link.active
                              ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                              : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                          }`}
                          dangerouslySetInnerHTML={{ __html: link.label }}
                          preserveScroll
                        />
                      );
                    })}
                  </nav>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
