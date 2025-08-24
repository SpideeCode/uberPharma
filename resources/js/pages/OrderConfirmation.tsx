import Nav from "@/components/Nav";

interface OrderConfirmationProps {
  order: {
    id: number;
    pharmacy?: {
      name?: string;
    };
    items: {
      id: number;
      product?: {
        name?: string;
      };
      quantity: number;
      price: number;
    }[];
  };
  estimatedMinutes?: number;
}

export default function OrderConfirmation({
  order,
  estimatedMinutes = 30,
}: OrderConfirmationProps) {
  const totalPrice = order.items.reduce(
    (sum, item) => sum + Number(item.price) * item.quantity,
    0
  );

  return (
    <div>
      <Nav />

      <div className="max-w-2xl mx-auto py-10">
        <h1 className="text-2xl font-bold mb-4 text-green-700">
          Confirmation de commande
        </h1>

        <p className="mb-2">
          Pharmacie : {order.pharmacy?.name || "Inconnue"}
        </p>
        <p className="mb-6">Livraison estimée : {estimatedMinutes} minutes</p>

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
      </div>
    </div>
  );
}
