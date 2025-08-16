import { useState } from "react";
import { router } from "@inertiajs/react";
import { Head } from "@inertiajs/react";

export default function EditProduct({ pharmacy, product }: any) {
  const [form, setForm] = useState({
    name: product.name,
    price: product.price,
    stock: product.stock,
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setForm(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    router.put(`/products/${product.id}`, form);
  };

  return (
    <div className="min-h-screen bg-white text-green-700">
      <Head title={`Modifier ${product.name}`} />
      <main className="max-w-3xl mx-auto p-6">
        <h1 className="text-2xl font-bold mb-4">Modifier {product.name}</h1>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block font-medium">Nom</label>
            <input
              type="text"
              name="name"
              value={form.name}
              onChange={handleChange}
              className="w-full rounded border p-2"
              required
            />
          </div>

          <div>
            <label className="block font-medium">Prix</label>
            <input
              type="number"
              name="price"
              value={form.price}
              onChange={handleChange}
              className="w-full rounded border p-2"
              step="0.01"
              required
            />
          </div>

          <div>
            <label className="block font-medium">Stock</label>
            <input
              type="number"
              name="stock"
              value={form.stock}
              onChange={handleChange}
              className="w-full rounded border p-2"
              required
            />
          </div>

          <div className="text-right">
            <button
              type="submit"
              className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
            >
              Enregistrer
            </button>
          </div>
        </form>
      </main>
    </div>
  );
}
