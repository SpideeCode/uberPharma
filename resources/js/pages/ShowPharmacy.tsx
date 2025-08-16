import { Link, Head } from "@inertiajs/react";

export default function ShowPharmacy({ pharmacy }: any) {
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
        
          <Link
            href={route("pharmacies.edit", pharmacy.id)}
            className="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            Modifier
          </Link>
    

        <div className="mb-4 text-gray-700">
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

        {/* Section produits (vide pour l'instant) */}
        <section>
          <h2 className="text-2xl font-bold mb-2">Produits disponibles</h2>
          <p className="text-gray-500">
            Cette section sera remplie lorsque les produits seront ajoutés.
          </p>
        </section>
      </main>
    </div>
  );
}
