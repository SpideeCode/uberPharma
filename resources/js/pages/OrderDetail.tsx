import Nav from "@/components/Nav";

interface Product {
  id: number;
  name: string;
  price: number;
}

interface OrderItem {
  id: number;
  product?: Product;
  quantity: number;
  price: number;
}

interface Pharmacy {
  id: number;
  name?: string;
}

interface Order {
  id: number;
  pharmacy?: Pharmacy;
  items: OrderItem[];
  created_at: string;
  total: number;
  status?: string; // par ex: 'en cours', 'livrée'
}

interface OrderDetailProps {
  order: Order;
  estimatedMinutes?: number;
}

export default function OrderDetail({
  order,
  estimatedMinutes = 30,
}: OrderDetailProps) {
  const totalPrice = order.items.reduce(
    (sum, item) => sum + Number(item.price) * item.quantity,
    0
  );

  return (
    <div>
      <Nav />

      <div className="max-w-2xl mx-auto py-10">
        <h1 className="text-2xl font-bold mb-4 text-green-700">
          Détail de la commande #{order.id}
        </h1>

        <p className="mb-2">
          Pharmacie : {order.pharmacy?.name || "Inconnue"}
        </p>
        <p className="mb-2">
          Statut : {order.status || "En attente"}
        </p>
        <p className="mb-6">
          Commande passée le : {new Date(order.created_at).toLocaleString()}
        </p>

        <div className="space-y-4">
          {order.items.length > 0 ? (
            order.items.map((item) => (
              <div
                key={item.id}
                className="flex justify-between border-b pb-2"
              >
                <div>
                  <p className="font-semibold">
                    {item.product?.name || "Produit inconnu"}
                  </p>
                  <p className="text-sm text-gray-600">
                    {item.quantity} × {Number(item.price).toFixed(2)} €
                  </p>
                </div>
                <p className="font-semibold">
                  {(Number(item.price) * item.quantity).toFixed(2)} €
                </p>
              </div>
            ))
          ) : (
            <p className="text-gray-500">Aucun produit dans cette commande.</p>
          )}
        </div>

        <div className="flex justify-between mt-6 text-lg font-bold">
          <span>Total :</span>
          <span>{totalPrice.toFixed(2)} €</span>
        </div>

        <p className="mt-4 text-sm text-gray-500">
          Livraison estimée : {estimatedMinutes} minutes
        </p>
      </div>
    </div>
  );
}
