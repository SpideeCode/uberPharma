import { Link } from "@inertiajs/react";
import Nav from "@/components/Nav";

interface Product {
  id: number;
  name: string;
  price: number;
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
}

interface Pharmacy {
  id: number;
  name: string;
}

interface Order {
  id: number;
  client?: Client;
  items: OrderItem[];
  created_at: string;
  status: string;
  total_price: number | string;
}

interface PharmacyOrdersProps {
  orders: Order[];
}

export default function PharmacyOrders({ orders }: PharmacyOrdersProps) {
  return (
    <div>
      <Nav />

      <div className="max-w-4xl mx-auto py-10">
        <h1 className="text-2xl font-bold mb-6 text-green-700">
          Commandes reçues
        </h1>

        {orders.length === 0 ? (
          <p className="text-gray-500">Aucune commande pour vos pharmacies.</p>
        ) : (
          <div className="space-y-6">
            {orders.map((order) => {
              const totalPrice = order.items.reduce(
                (sum, item) => sum + Number(item.price) * item.quantity,
                0
              );

              return (
                <div
                  key={order.id}
                  className="border rounded-lg p-4 shadow-sm hover:shadow-md transition"
                >
                  <div className="flex justify-between items-center mb-2">
                    <p className="font-semibold">
                      Commande #{order.id} - Client:{" "}
                      {order.client?.name || "Inconnu"}
                    </p>
                    <p className="text-sm text-gray-500">
                      {new Date(order.created_at).toLocaleDateString()}{" "}
                      {new Date(order.created_at).toLocaleTimeString()}
                    </p>
                  </div>

                  <div className="mb-2">
                    {order.items.map((item) => (
                      <div
                        key={item.id}
                        className="flex justify-between text-gray-700 text-sm"
                      >
                        <span>{item.product?.name || "Produit inconnu"}</span>
                        <span>
                          {item.quantity} × {Number(item.price).toFixed(2)} €
                        </span>
                      </div>
                    ))}
                  </div>

                  <div className="flex justify-between mt-2 font-bold">
                    <span>Total :</span>
                    <span>{Number(order.total_price ?? totalPrice).toFixed(2)} €</span>
                  </div>

                  <Link
                    href={`/orders/${order.id}`}
                    className="mt-3 inline-block text-green-600 hover:underline"
                  >
                    Voir le détail
                  </Link>
                </div>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
}
