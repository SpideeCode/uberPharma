import { usePage, router, Link } from "@inertiajs/react";
import { useState } from "react";
import Nav from '../components/Nav';
import { type SharedData } from "@/types";


export default function AdminProducts() {
  const { products, pharmacies } = usePage().props;
  const { auth } = usePage<SharedData>().props;
    console.log(auth);
  const [newProduct, setNewProduct] = useState({
    name: "",
    price: 0,
    stock: 0,
    pharmacy_id: "",
  });

  const handleNewProductChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>
  ) => {
    setNewProduct((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleCreateProduct = (e: React.FormEvent) => {
    e.preventDefault();
    router.post("/products", newProduct, {
      onSuccess: () =>
        setNewProduct({ name: "", price: 0, stock: 0, pharmacy_id: "" }),
    });
  };

  const handleDeleteProduct = (id: number) => {
    if (!confirm("Voulez-vous vraiment supprimer ce produit ?")) return;
    router.delete(`/products/${id}`);
  };

  return (
    <div className="min-h-screen bg-white text-black">
        <Nav />
      <div className="p-6">
        <h1 className="text-3xl font-bold mb-6">Gestion des produits</h1>

        {/* Formulaire création produit */}
        <form
          onSubmit={handleCreateProduct}
          className="mb-8 rounded border border-gray-300 bg-gray-50 p-4"
        >
          <h2 className="mb-4 text-xl font-semibold">Ajouter un produit</h2>
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input
              type="text"
              name="name"
              placeholder="Nom du produit"
              value={newProduct.name}
              onChange={handleNewProductChange}
              className="rounded border border-gray-400 px-3 py-2 focus:ring focus:ring-gray-400 focus:outline-none"
              required
            />
            <input
              type="number"
              name="price"
              placeholder="Prix"
              value={newProduct.price}
              onChange={handleNewProductChange}
              className="rounded border border-gray-400 px-3 py-2 focus:ring focus:ring-gray-400 focus:outline-none"
              required
            />
            <input
              type="number"
              name="stock"
              placeholder="Stock"
              value={newProduct.stock}
              onChange={handleNewProductChange}
              className="rounded border border-gray-400 px-3 py-2 focus:ring focus:ring-gray-400 focus:outline-none"
              required
            />
            <select
              name="pharmacy_id"
              value={newProduct.pharmacy_id}
              onChange={handleNewProductChange}
              className="rounded border border-gray-400 px-3 py-2 focus:ring focus:ring-gray-400 focus:outline-none"
              required
            >
              <option value="">Sélectionner une pharmacie</option>
              {/* {pharmacies.map((pharmacy: any) => (
                <option key={pharmacy.id} value={pharmacy.id}>
                  {pharmacy.name}
                </option>
              ))} */}
            </select>
          </div>
          <button
            type="submit"
            className="mt-4 rounded bg-green-600 px-6 py-2 text-white hover:bg-green-700 transition"
          >
            Ajouter
          </button>
        </form>

        {/* Liste des produits */}
        <div className="hidden md:block">
          <table className="w-full border-collapse border border-gray-300">
            <thead>
              <tr className="bg-gray-100">
                <th className="border border-gray-300 px-4 py-2">ID</th>
                <th className="border border-gray-300 px-4 py-2">Nom</th>
                <th className="border border-gray-300 px-4 py-2">Pharmacie</th>
                <th className="border border-gray-300 px-4 py-2">Prix</th>
                <th className="border border-gray-300 px-4 py-2">Stock</th>
                <th className="border border-gray-300 px-4 py-2 text-center">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody>
              {products.map((product: any) => (
                <tr key={product.id} className="hover:bg-gray-50">
                  <td className="border border-gray-300 px-4 py-2">
                    {product.id}
                  </td>
                  <td className="border border-gray-300 px-4 py-2">
                    {product.name}
                  </td>
                  <td className="border border-gray-300 px-4 py-2">
                    {product.pharmacy?.name}
                  </td>
                  <td className="border border-gray-300 px-4 py-2">
                    {product.price} €
                  </td>
                  <td className="border border-gray-300 px-4 py-2">
                    {product.stock}
                  </td>
                  <td className="border border-gray-300 px-4 py-2 text-center space-x-2">
                    <Link
                      href={`/products/${product.id}/edit`}
                      className="rounded bg-blue-600 px-3 py-1 text-white hover:bg-blue-700"
                    >
                      Éditer
                    </Link>
                    <button
                      onClick={() => handleDeleteProduct(product.id)}
                      className="rounded bg-red-600 px-3 py-1 text-white hover:bg-red-700"
                    >
                      Supprimer
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Mobile cards */}
        <div className="space-y-4 md:hidden">
          {products.map((product: any) => (
            <div
              key={product.id}
              className="rounded border border-gray-300 bg-gray-50 p-4 shadow-sm"
            >
              <p>
                <span className="font-semibold">ID :</span> {product.id}
              </p>
              <p>
                <span className="font-semibold">Nom :</span> {product.name}
              </p>
              <p>
                <span className="font-semibold">Pharmacie :</span>{" "}
                {product.pharmacy?.name}
              </p>
              <p>
                <span className="font-semibold">Prix :</span> {product.price} €
              </p>
              <p>
                <span className="font-semibold">Stock :</span> {product.stock}
              </p>
              <div className="flex gap-2 mt-2">
                <Link
                  href={`/products/${product.id}/edit`}
                  className="flex-1 rounded bg-blue-600 px-4 py-2 text-white text-center hover:bg-blue-700"
                >
                  Éditer
                </Link>
                <button
                  onClick={() => handleDeleteProduct(product.id)}
                  className="flex-1 rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700"
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
