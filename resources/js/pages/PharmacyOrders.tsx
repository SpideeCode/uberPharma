import { Head, Link, router } from "@inertiajs/react";
import Nav from "@/components/Nav";
import { useState } from 'react';
import { CheckCircle, Clock, XCircle, Truck, RefreshCw } from 'lucide-react';

interface Product {
  id: number;
  name: string;
  price: number;
  image?: string;
}

interface OrderItem {
  id: number;
  product: Product;
  quantity: number;
  price: number;
}

interface Client {
  id: number;
  name: string;
  email?: string;
  phone?: string;
}

interface Pharmacy {
  id: number;
  name: string;
  address?: string;
}

interface StatusConfigType {
  label: string;
  icon: React.ComponentType<{ className?: string }>;
  color: string;
  bgColor: string;
  borderColor: string;
  nextStatus?: string;
  buttonText?: string;
  buttonClass?: string;
  canCancel?: boolean;
  className?: string;
}

interface Order {
  id: number;
  client?: Client;
  items: OrderItem[];
  created_at: string;
  updated_at: string;
  status: 'pending' | 'accepted' | 'in_delivery' | 'delivered' | 'cancelled';
  total_price: number | string;
  delivery_address: string;
  delivery_phone?: string;
  notes?: string;
  pharmacy?: Pharmacy;
  payment_status: 'unpaid' | 'paid' | 'refunded';
}

interface PharmacyOrdersProps {
  orders: Order[];
}

const statusConfig: Record<string, StatusConfigType> = {
  pending: {
    label: 'En attente',
    icon: Clock,
    className: 'border-yellow-200 bg-yellow-50 text-yellow-800',
    color: 'text-yellow-600',
    bgColor: 'bg-yellow-100',
    borderColor: 'border-yellow-200',
    nextStatus: 'accepted',
    buttonText: 'Accepter',
    buttonClass: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
    canCancel: true
  },
  accepted: {
    label: 'Acceptée',
    icon: CheckCircle,
    className: 'border-blue-200 bg-blue-50 text-blue-800',
    color: 'text-blue-600',
    bgColor: 'bg-blue-100',
    borderColor: 'border-blue-200',
    nextStatus: 'in_delivery',
    buttonText: 'Mettre en livraison',
    buttonClass: 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
    canCancel: true
  },
  in_delivery: {
    label: 'En cours de livraison',
    icon: Truck,
    className: 'border-purple-200 bg-purple-50 text-purple-800',
    color: 'text-purple-600',
    bgColor: 'bg-purple-100',
    borderColor: 'border-purple-200',
    nextStatus: 'delivered',
    buttonText: 'Marquer comme livrée',
    buttonClass: 'bg-purple-600 hover:bg-purple-700 focus:ring-purple-500',
    canCancel: false
  },
  delivered: {
    label: 'Livrée',
    icon: CheckCircle,
    className: 'border-green-200 bg-green-50 text-green-800',
    color: 'text-green-600',
    bgColor: 'bg-green-100',
    borderColor: 'border-green-200',
    buttonClass: 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
    canCancel: false
  },
  cancelled: {
    label: 'Annulée',
    icon: XCircle,
    className: 'border-red-200 bg-red-50 text-red-800',
    color: 'text-red-600',
    bgColor: 'bg-red-100',
    borderColor: 'border-red-200',
    buttonClass: 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
    canCancel: false
  }
};

export default function PharmacyOrders({ orders: initialOrders }: PharmacyOrdersProps) {
  const [orders, setOrders] = useState<Order[]>(initialOrders);
  const [isUpdating, setIsUpdating] = useState<number | null>(null);

  // Groupe les commandes par statut
  const ordersByStatus = orders.reduce((acc, order) => {
    acc[order.status] = acc[order.status] || [];
    acc[order.status].push(order);
    return acc;
  }, {} as Record<string, Order[]>);

  // Met à jour le statut d'une commande
  const updateOrderStatus = async (orderId: number, newStatus: string) => {
    try {
      console.log(`Tentative de mise à jour de la commande ${orderId} vers le statut ${newStatus}`);
      setIsUpdating(orderId);
      
      const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      if (!csrfToken) {
        throw new Error('CSRF token not found');
      }
      
      console.log('Envoi de la requête PATCH...');
      const response = await fetch(`/api/orders/${orderId}/status`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'same-origin',
        body: JSON.stringify({ status: newStatus })
      });

      console.log('Réponse reçue:', response.status, response.statusText);
      
      if (!response.ok) {
        const errorText = await response.text();
        console.error('Réponse d\'erreur du serveur:', errorText);
        throw new Error(`Erreur ${response.status}: ${response.statusText}. Détails: ${errorText}`);
      }
      
      const responseData = await response.json().catch(e => {
        console.error('Erreur lors du parsing de la réponse JSON:', e);
        throw new Error('Réponse invalide du serveur');
      });
      
      console.log('Réponse du serveur:', responseData);
      
      if (!responseData.order) {
        throw new Error('Réponse du serveur invalide: ordre manquant');
      }
      
      setOrders(prevOrders => 
        prevOrders.map(order => 
          order.id === orderId ? { ...order, ...responseData.order } : order
        )
      );
      
      console.log('Mise à jour du statut réussie');
    } catch (error) {
      console.error('Erreur détaillée lors de la mise à jour du statut:', error);
      alert(`Erreur lors de la mise à jour du statut: ${error instanceof Error ? error.message : 'Erreur inconnue'}`);
    } finally {
      setIsUpdating(null);
    }
  };

  // Formate la date
  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  // Formate le prix
  const formatPrice = (price: number | string) => {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR'
    }).format(Number(price));
  };

  // Composant de statut
  const StatusBadge = ({ status }: { status: string }) => {
    const config = statusConfig[status as keyof typeof statusConfig] || statusConfig.pending;
    const Icon = config.icon;
    
    return (
      <span className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${config.color}`}>
        <Icon className="h-3 w-3 mr-1" />
        {config.label}
      </span>
    );
  };

  // Composant de carte de commande
  const OrderCard = ({ order }: { order: Order }) => {
    const config = statusConfig[order.status] || statusConfig.pending;
    
    return (
      <div className={`mb-6 overflow-hidden rounded-lg border ${config.borderColor} ${config.bgColor}`}>
        <div className="p-4">
          <div className="flex justify-between items-start">
            <div>
              <h3 className="text-lg font-semibold">Commande #{order.id}</h3>
              <p className="text-sm text-gray-500">{formatDate(order.created_at)}</p>
            </div>
            <StatusBadge status={order.status} />
          </div>

          {order.client && (
            <div className="mt-3 p-3 bg-white rounded-md border">
              <h4 className="font-medium">Client</h4>
              <p className="text-sm">{order.client.name}</p>
              {order.client.phone && <p className="text-sm text-gray-600">{order.client.phone}</p>}
              {order.client.email && <p className="text-sm text-gray-600">{order.client.email}</p>}
              
              {order.delivery_address && (
                <div className="mt-2 pt-2 border-t">
                  <h4 className="font-medium">Adresse de livraison</h4>
                  <p className="text-sm text-gray-600">{order.delivery_address}</p>
                  {order.delivery_phone && <p className="text-sm text-gray-600">Tél: {order.delivery_phone}</p>}
                </div>
              )}
            </div>
          )}

          <div className="mt-3">
            <h4 className="font-medium mb-2">Produits commandés</h4>
            <div className="space-y-2">
              {order.items.map((item) => (
                <div key={item.id} className="flex justify-between items-center p-2 bg-white rounded border">
                  <div className="flex items-center">
                    {item.product.image ? (
                      <img 
                        src={item.product.image} 
                        alt={item.product.name}
                        className="w-10 h-10 object-cover rounded mr-3"
                      />
                    ) : (
                      <div className="w-10 h-10 bg-gray-200 rounded mr-3 flex items-center justify-center">
                        <Truck className="h-5 w-5 text-gray-400" />
                      </div>
                    )}
                    <div>
                      <p className="text-sm font-medium">{item.product.name}</p>
                      <p className="text-xs text-gray-500">{item.quantity} x {formatPrice(item.price)}</p>
                    </div>
                  </div>
                  <span className="text-sm font-medium">
                    {formatPrice(item.price * item.quantity)}
                  </span>
                </div>
              ))}
            </div>
          </div>

          {order.notes && (
            <div className="mt-3 p-3 bg-yellow-50 border-l-4 border-yellow-400">
              <p className="text-sm text-yellow-700">
                <span className="font-medium">Note :</span> {order.notes}
              </p>
            </div>
          )}

          <div className="mt-4 pt-3 border-t flex justify-between items-center">
            <div className="font-semibold">
              Total : {formatPrice(order.total_price)}
              {order.payment_status === 'paid' && (
                <span className="ml-2 text-xs font-normal text-green-600">
                  (Payée)
                </span>
              )}
            </div>
            
            <div className="flex flex-wrap gap-2 mt-2">
              {statusConfig[order.status]?.nextStatus && (
                <button
                  onClick={() => {
                    const nextStatus = statusConfig[order.status]?.nextStatus;
                    if (nextStatus) {
                      updateOrderStatus(order.id, nextStatus);
                    }
                  }}
                  disabled={isUpdating === order.id}
                  className={`px-4 py-2 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 ${statusConfig[order.status]?.buttonClass} disabled:opacity-50 flex items-center`}
                >
                  {isUpdating === order.id ? (
                    <>
                      <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Traitement...
                    </>
                  ) : (
                    <>
                      {(() => {
                        const Icon = statusConfig[order.status]?.icon;
                        return Icon ? <Icon className="w-4 h-4 mr-2" /> : null;
                      })()}
                      {statusConfig[order.status]?.buttonText}
                    </>
                  )}
                </button>
              )}
              
              {statusConfig[order.status]?.canCancel && (
                <button
                  onClick={() => updateOrderStatus(order.id, 'cancelled')}
                  disabled={isUpdating === order.id}
                  className="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 flex items-center"
                >
                  <XCircle className="w-4 h-4 mr-2" />
                  Annuler
                </button>
              )}
              
              {order.status === 'delivered' && (
                <span className="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium bg-green-100 text-green-800">
                  <CheckCircle className="w-4 h-4 mr-2" />
                  Commande livrée
                </span>
              )}
              
              {order.status === 'cancelled' && (
                <span className="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium bg-red-100 text-red-800">
                  <XCircle className="w-4 h-4 mr-2" />
                  Commande annulée
                </span>
              )}
            </div>
          </div>
        </div>
      </div>
    );
  };

  // Affiche les commandes par statut
  const renderOrdersByStatus = (status: string, title: string) => {
    const ordersList = ordersByStatus[status] || [];
    if (ordersList.length === 0) return null;
    
    return (
      <div key={status} className="mb-8">
        <h2 className="text-xl font-semibold mb-4">{title} ({ordersList.length})</h2>
        <div className="space-y-4">
          {ordersList.map((order: Order) => (
            <OrderCard key={order.id} order={order} />
          ))}
        </div>
      </div>
    );
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Head title="Gestion des commandes" />
      <Nav />
      
      <div className="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center mb-8">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Gestion des commandes</h1>
            <p className="mt-1 text-sm text-gray-500">
              Gérez les commandes de vos pharmacies
            </p>
          </div>
          <Link
            href="/pharmacy/dashboard"
            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700"
          >
            Retour au tableau de bord
          </Link>
        </div>

        {orders.length === 0 ? (
          <div className="bg-white rounded-lg shadow p-8 text-center">
            <Truck className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-lg font-medium text-gray-900">Aucune commande</h3>
            <p className="mt-1 text-sm text-gray-500">
              Aucune commande n'a été passée pour le moment.
            </p>
          </div>
        ) : (
          <>
            {renderOrdersByStatus('pending', 'Nouvelles commandes')}
            {renderOrdersByStatus('accepted', 'Commandes acceptées')}
            {renderOrdersByStatus('in_delivery', 'En cours de livraison')}
            {renderOrdersByStatus('delivered', 'Commandes livrées')}
            {renderOrdersByStatus('cancelled', 'Commandes annulées')}
          </>
        )}
      </div>
    </div>
  );
}
