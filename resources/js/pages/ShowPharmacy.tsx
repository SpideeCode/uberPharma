import { Link, Head, router } from "@inertiajs/react";

export default function ShowPharmacy({ pharmacy }: any) {
  const handleDelete = (productId: number) => {
    if (confirm("Supprimer ce produit ?")) {
      // On utilise directement l'URL plutôt que route() pour simplifier
      router.delete(`/products/${productId}`, {
        onSuccess: () => {
          alert("Produit supprimé");
        },
      });
    }
  };

  return (
    <div className="min-h-screen bg-white text-green-700">
      <Head title={pharmacy.name} />

      {/* Header */}
      <header className="bg-green-700 text-white p-6 flex justify-between items-center shadow-md">
        <Link href="/" className="text-xl font-bold">
          ← Retour à l’accueil
        </Link>
      </header>

      {/* Contenu principal */}
      <main className="max-w-3xl mx-auto p-6">
        <h1 className="text-3xl font-bold mb-4">{pharmacy.name}</h1>

        {/* Modifier la pharmacie */}
        <Link
          href={`/pharmacies/${pharmacy.id}/edit`}
          className="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          Modifier la pharmacie
        </Link>

        {/* Infos pharmacie */}
        <div className="mb-4 text-gray-700 mt-4">
          <p>
            <span className="font-medium">Adresse :</span>{" "}
            {pharmacy.address || "Non renseignée"}
          </p>
          <p>
            <span className="font-medium">Téléphone :</span>{" "}
            {pharmacy.phone || "Non renseigné"}
          </p>
          <p>
            <span className="font-medium">Propriétaire :</span>{" "}
            {pharmacy.user ? pharmacy.user.name : "Inconnu"}
          </p>
        </div>

        {/* Ajouter un produit */}
        <Link
          href={`/products/create?pharmacy_id=${pharmacy.id}`}
          className="mb-4 inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
        >
          Ajouter un produit
        </Link>

        {/* Section produits */}
        <section>
          <h2 className="text-2xl font-bold mb-2">Produits disponibles</h2>

          {pharmacy.products && pharmacy.products.length > 0 ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              {pharmacy.products.map((product: any) => (
                <div key={product.id} className="border rounded p-4 shadow">
                  <h3 className="font-semibold text-lg">{product.name}</h3>
                  <p>Prix : {product.price} €</p>
                  <p>Stock : {product.stock}</p>
                  <div className="mt-2 flex gap-2">
                    <Link
                      href={`/products/${product.id}/edit`}
                      className="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600"
                    >
                      Modifier
                    </Link>
                    <button
                      onClick={() => handleDelete(product.id)}
                      className="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700"
                    >
                      Supprimer
                    </button>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-gray-500">
              Aucun produit disponible pour le moment.
            </p>
          )}
        </section>
      </main>
    </div>
  );
}
