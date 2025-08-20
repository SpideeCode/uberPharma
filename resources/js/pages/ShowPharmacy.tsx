import { type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import Nav from '../components/Nav';
import CartSidebar from '../components/CartSidebar';
import { useState } from 'react';

export default function ShowPharmacy({ pharmacy, cart }: { pharmacy: any, cart: any }) {
    const { auth } = usePage<SharedData>().props;
    const [isCartOpen, setIsCartOpen] = useState(false);

    return (
        <div className="min-h-screen bg-white text-green-700 relative">
            <Nav />
            <Head title={pharmacy.name} />

            {/* Header */}
            <header className="flex items-center justify-between bg-green-700 p-6 text-white shadow-md">
                <Link href="/" className="text-xl font-bold">
                    ← Retour à l’accueil
                </Link>

                {auth?.role === 'client' && (
                    <button
                        onClick={() => setIsCartOpen(!isCartOpen)}
                        className="rounded bg-white px-4 py-2 text-green-700 shadow hover:bg-gray-200"
                    >
                        🛒 Panier ({cart?.items?.length ?? 0})
                    </button>
                )}
            </header>

            {/* Contenu principal */}
            <main className="mx-auto max-w-3xl p-6">
                <h1 className="mb-4 text-3xl font-bold">{pharmacy.name}</h1>

                <div className="mt-4 mb-4 text-gray-700">
                    <p><span className="font-medium">Adresse :</span> {pharmacy.address || 'Non renseignée'}</p>
                    <p><span className="font-medium">Téléphone :</span> {pharmacy.phone || 'Non renseigné'}</p>
                    <p><span className="font-medium">Propriétaire :</span> {pharmacy.user?.name || 'Inconnu'}</p>
                </div>

                {/* Ajouter un produit pour pharmacy/admin */}
                {(auth?.role === 'pharmacy' || auth?.role === 'admin') && (
                    <div className="mb-4">
                        <Link
                            href={`/products/create?pharmacy_id=${pharmacy.id}`}
                            className="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
                        >
                            + Ajouter un produit
                        </Link>
                    </div>
                )}

                <section>
                    <h2 className="mb-2 text-2xl font-bold">Produits disponibles</h2>
                    {pharmacy.products?.length > 0 ? (
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            {pharmacy.products.map((product: any) => (
                                <div key={product.id} className="rounded border p-4 shadow">
                                    <h3 className="text-lg font-semibold">{product.name}</h3>
                                    <p>Prix : {product.price} €</p>
                                    <p>Stock : {product.stock}</p>

                                    {/* Actions pour clients */}
                                    {auth?.role === 'client' && (
                                        <div className="mt-2 flex gap-2">
                                            <input
                                                type="number"
                                                min={1}
                                                defaultValue={1}
                                                className="w-16 rounded border px-1"
                                                id={`quantity-${product.id}`}
                                            />
                                            <button
                                                onClick={(e) => {
                                                    e.preventDefault();
                                                    const qty = Number(
                                                        (document.getElementById(
                                                            `quantity-${product.id}`
                                                        ) as HTMLInputElement).value
                                                    );
                                                    router.post(route('cart.add'), {
                                                        product_id: product.id,
                                                        quantity: qty,
                                                        pharmacy_id: pharmacy.id,
                                                    });
                                                }}
                                                className="rounded bg-green-600 px-3 py-1 text-white hover:bg-green-700"
                                            >
                                                Ajouter au panier
                                            </button>
                                        </div>
                                    )}

                                    {/* Actions pour pharmacy/admin */}
                                    {(auth?.role === 'pharmacy' || auth?.role === 'admin') && (
                                        <div className="mt-2 flex gap-2">
                                            <Link
                                                href={`/products/${product.id}/edit`}
                                                className="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
                                            >
                                                Modifier
                                            </Link>
                                            <button
                                                onClick={() => {
                                                    if (!confirm('Voulez-vous vraiment supprimer ce produit ?')) return;
                                                    router.delete(`/products/${product.id}`);
                                                }}
                                                className="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700"
                                            >
                                                Supprimer
                                            </button>
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="text-gray-500">Aucun produit disponible pour le moment.</p>
                    )}
                </section>
            </main>

            {/* CartSidebar */}
            {auth?.role === 'client' && (
                <CartSidebar
                    isOpen={isCartOpen}
                    toggle={() => setIsCartOpen(false)}
                    cart={cart ?? null}
                />
            )}
        </div>
    );
}
