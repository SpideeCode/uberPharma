import { router } from "@inertiajs/react";

interface CartSidebarProps {
    isOpen: boolean;
    toggle: () => void;
    cart: {
        id: number; // cart_id
        items: {
            id: number; // cart_item_id
            product: {
                id: number;
                name: string;
                price: number;
            };
            quantity: number;
        }[];
    } | null;
}

export default function CartSidebar({ isOpen, toggle, cart }: CartSidebarProps) {

    const removeFromCart = (cartItemId: number) => {
        router.post('/cart/remove', { cart_item_id: cartItemId }, {
            onSuccess: () => console.log('Produit retiré du panier'),
        });
    };

    const clearCart = () => {
        if (!cart) return;
        router.post('/cart/clear', { cart_id: cart.id }, {
            onSuccess: () => console.log('Panier vidé'),
        });
    };

    const totalPrice = cart?.items.reduce(
        (sum, item) => sum + item.product.price * item.quantity, 
        0
    ) ?? 0;

    return (
        <div
            className={`fixed top-0 right-0 h-full w-80 bg-white shadow-2xl transform transition-transform duration-300 z-50 ${
                isOpen ? 'translate-x-0' : 'translate-x-full'
            }`}
        >
            {/* Header */}
            <div className="flex justify-between items-center p-4 border-b">
                <h2 className="text-xl font-bold text-green-700">Mon panier</h2>
                <button onClick={toggle} className="text-red-600 hover:text-red-800">✕</button>
            </div>

            {/* Contenu */}
            <div className="p-4 overflow-y-auto h-[calc(100%-120px)] space-y-4">
                {cart && cart.items.length > 0 ? (
                    cart.items.map(item => (
                        <div key={item.id} className="flex justify-between items-center border-b pb-2">
                            <div>
                                <p className="font-semibold">{item.product.name}</p>
                                <p className="text-sm text-gray-600">{item.quantity} × {item.product.price} €</p>
                            </div>
                            <button
                                onClick={() => removeFromCart(item.id)}
                                className="text-red-500 hover:text-red-700"
                            >
                                Supprimer
                            </button>
                        </div>
                    ))
                ) : (
                    <p className="text-gray-500">Votre panier est vide.</p>
                )}
            </div>

            {/* Footer */}
            <div className="p-4 border-t">
                <p className="mb-2 font-semibold">Total : {totalPrice.toFixed(2)} €</p>
                <button
                    onClick={clearCart}
                    className="w-full rounded bg-red-600 py-2 text-white hover:bg-red-700 mb-2"
                >
                    Vider le panier
                </button>
                <button className="w-full rounded bg-green-600 py-2 text-white hover:bg-green-700">
                    Passer commande
                </button>
            </div>
        </div>
    );
}
