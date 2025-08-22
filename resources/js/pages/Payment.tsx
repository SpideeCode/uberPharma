import { router } from "@inertiajs/react";
import Nav from "@/components/Nav";

interface PaymentProps {
  cart: {
    id: number;
    items: {
      id: number;
      quantity: number;
      product: {
        id: number;
        name: string;
        price: number;
      };
    }[];
  } | null;
}

export default function Payment({ cart }: PaymentProps) {
  const total =
    cart?.items.reduce(
      (sum, item) => sum + item.product.price * item.quantity,
      0
    ) ?? 0;

  const handlePayment = (e: React.FormEvent) => {
    e.preventDefault();

    if (!cart) return;

    // Simulation de paiement
    router.post("/payment", {
      cart_id: cart.id,
      // tu peux ajouter des infos de livraison ici si tu veux
    });
  };

  return (
    <div>
      <Nav />

      <div className="max-w-2xl mx-auto py-10">
        <h1 className="text-2xl font-bold mb-6 text-green-700">Paiement</h1>

        {cart && cart.items.length > 0 ? (
          <form onSubmit={handlePayment} className="space-y-4">
            {cart.items.map((item) => (
              <div key={item.id} className="flex justify-between border-b pb-2">
                <div>
                  <p className="font-semibold">{item.product.name}</p>
                  <p className="text-sm text-gray-600">
                    {item.quantity} × {item.product.price} €
                  </p>
                </div>
                <p className="font-semibold">
                  {(item.quantity * item.product.price).toFixed(2)} €
                </p>
              </div>
            ))}

            <div className="flex justify-between mt-6 text-lg font-bold">
              <span>Total :</span>
              <span>{total.toFixed(2)} €</span>
            </div>

            {/* Options de paiement */}
            <div className="mt-8">
              <h2 className="text-xl font-semibold mb-4">Méthode de paiement</h2>
              <div className="space-y-3">
                <label className="flex items-center space-x-2">
                  <input type="radio" name="payment" defaultChecked />
                  <span>Carte bancaire</span>
                </label>
                <label className="flex items-center space-x-2">
                  <input type="radio" name="payment" />
                  <span>PayPal</span>
                </label>
                <label className="flex items-center space-x-2">
                  <input type="radio" name="payment" />
                  <span>Bancontact</span>
                </label>
              </div>
            </div>

            <button
              type="submit"
              className="mt-6 w-full rounded bg-green-600 py-3 text-white font-bold hover:bg-green-700"
            >
              Confirmer et payer
            </button>
          </form>
        ) : (
          <p className="text-gray-500">Votre panier est vide.</p>
        )}
      </div>
    </div>
  );
}
