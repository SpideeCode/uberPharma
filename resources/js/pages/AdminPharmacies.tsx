import { router, usePage, Link } from '@inertiajs/react';
import { useState } from 'react';

export default function AdminPharmacies() {
  const { pharmacies } = usePage().props;
  const [newPharmacy, setNewPharmacy] = useState({
    name: '',
    address: '',
    phone: '',
  });

  const handleNewPharmacyChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setNewPharmacy((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleCreatePharmacy = (e: React.FormEvent) => {
    e.preventDefault();
    router.post(route('pharmacies.store'), newPharmacy, {
      onSuccess: () => setNewPharmacy({ name: '', address: '', phone: '' }),
    });
  };

  const handleDeletePharmacy = (id: number) => {
    if (!confirm('Voulez-vous vraiment supprimer cette pharmacie ?')) return;
    router.delete(route('pharmacies.destroy', id));
  };

  return (
    <div >
      <div className="p-6">
        <h1 className="text-3xl font-bold mb-6">Gestion des pharmacies</h1>

        <form onSubmit={handleCreatePharmacy} className="mb-8 rounded border border-gray-300 bg-gray-50 p-4">
          <h2 className="mb-4 text-xl font-semibold">Ajouter une nouvelle pharmacie</h2>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
            <input
              type="text"
              name="name"
              placeholder="Nom"
              value={newPharmacy.name}
              onChange={handleNewPharmacyChange}
              className="rounded border border-gray-400 px-3 py-2 focus:ring focus:ring-gray-400 focus:outline-none"
              required
            />
            <input
              type="text"
              name="address"
              placeholder="Adresse"
              value={newPharmacy.address}
              onChange={handleNewPharmacyChange}
              className="rounded border border-gray-400 px-3 py-2 focus:ring focus:ring-gray-400 focus:outline-none"
            />
            <input
              type="text"
              name="phone"
              placeholder="Téléphone"
              value={newPharmacy.phone}
              onChange={handleNewPharmacyChange}
              className="rounded border border-gray-400 px-3 py-2 focus:ring focus:ring-gray-400 focus:outline-none"
            />
          </div>
          <button type="submit" className="mt-4 rounded bg-green-700 px-6 py-2 text-white hover:bg-green-800 transition">
            Ajouter
          </button>
        </form>

        {/* Desktop Table */}
        <div className="hidden md:block">
          <table className="w-full border-collapse border border-gray-300">
            <thead>
              <tr className="bg-gray-100">
                <th className="border border-gray-300 px-4 py-2 text-left">ID</th>
                <th className="border border-gray-300 px-4 py-2 text-left">Nom</th>
                <th className="border border-gray-300 px-4 py-2 text-left">Adresse</th>
                <th className="border border-gray-300 px-4 py-2 text-left">Téléphone</th>
                <th className="border border-gray-300 px-4 py-2 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              {pharmacies.map((pharmacy: any) => (
                <tr key={pharmacy.id} className="hover:bg-gray-50">
                  <td className="border border-gray-300 px-4 py-2">{pharmacy.id}</td>
                  <td className="border border-gray-300 px-4 py-2">{pharmacy.name}</td>
                  <td className="border border-gray-300 px-4 py-2">{pharmacy.address}</td>
                  <td className="border border-gray-300 px-4 py-2">{pharmacy.phone}</td>
                  <td className="space-x-2 border border-gray-300 px-4 py-2 text-center">
                    <Link
                      href={route('pharmacies.edit', pharmacy.id)}
                      className="rounded bg-yellow-500 px-3 py-1 text-white hover:bg-yellow-600"
                    >
                      Modifier
                    </Link>
                    <button
                      onClick={() => handleDeletePharmacy(pharmacy.id)}
                      className="rounded bg-red-600 px-3 py-1 text-white hover:bg-red-700"
                      type="button"
                    >
                      Supprimer
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Mobile Cards */}
        <div className="space-y-4 md:hidden">
          {pharmacies.map((pharmacy: any) => (
            <div key={pharmacy.id} className="rounded border border-gray-300 bg-gray-50 p-4 shadow-sm">
              <p><span className="font-semibold">ID :</span> {pharmacy.id}</p>
              <p><span className="font-semibold">Nom :</span> {pharmacy.name}</p>
              <p><span className="font-semibold">Adresse :</span> {pharmacy.address}</p>
              <p><span className="font-semibold">Téléphone :</span> {pharmacy.phone}</p>
              <div className="mt-2 flex gap-2">
                <Link
                  href={route('pharmacies.edit', pharmacy.id)}
                  className="flex-1 rounded bg-yellow-500 px-4 py-2 text-white hover:bg-yellow-600 text-center"
                >
                  Modifier
                </Link>
                <button
                  onClick={() => handleDeletePharmacy(pharmacy.id)}
                  className="flex-1 rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                  type="button"
                >
                  Supprimer
                </button>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
