import Nav from "@/components/Nav";

interface OrderConfirmationProps {
    order: {
        id: number;
        total_price: number;
        items: {
            id: number;
            quantity: number;
            product: {
                id: number;
                name: string;
                price: number;
            };
        }[];
    };
    estimatedMinutes: number;
}

export default function OrderConfirmation({ order, estimatedMinutes }: OrderConfirmationProps) {
    if (!order || !order.items) return <p>Commande introuvable</p>;

    return (
        <div>
            <Nav />
        
        <div className="max-w-2xl mx-auto py-10">
            <h1 className="text-2xl font-bold mb-6 text-green-700">Commande confirmée</h1>

            <h2 className="text-lg font-semibold mb-4">Votre commande :</h2>
            {order.items.length > 0 ? (
                order.items.map(item => (
                    <div key={item.id} className="flex justify-between border-b pb-2">
                        <span>{item.product.name} x {item.quantity}</span>
                        <span>{(item.product.price * item.quantity).toFixed(2)} €</span>
                    </div>
                ))
            ) : (
                <p>Aucun produit dans cette commande.</p>
            )}

            <div className="mt-4 font-bold flex justify-between">
                <span>Total estimé :</span>
                <span>{Number(order.total_price).toFixed(2)} €</span>
            </div>

            <p className="mt-2">Temps estimé de livraison : {estimatedMinutes} minutes</p>
        </div>
        </div>
    );
}
