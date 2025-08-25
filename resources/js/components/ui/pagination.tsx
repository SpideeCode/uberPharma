import { Link } from "@inertiajs/react";

interface PaginationProps {
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

export function Pagination({ links }: PaginationProps) {
  if (links.length <= 3) return null;

  return (
    <nav className="flex items-center justify-between px-4 sm:px-0">
      <div className="-mt-px flex w-0 flex-1">
        {links[0].url && (
          <Link
            href={links[0].url}
            className="inline-flex items-center border-t-2 border-transparent pr-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
          >
            &larr; Précédent
          </Link>
        )}
      </div>
      
      <div className="hidden md:-mt-px md:flex">
        {links.slice(1, -1).map((link, index) => (
          <Link
            key={index}
            href={link.url || '#'}
            className={`inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium ${
              link.active
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
            }`}
          >
            {link.label}
          </Link>
        ))}
      </div>
      
      <div className="-mt-px flex w-0 flex-1 justify-end">
        {links[links.length - 1].url && (
          <Link
            href={links[links.length - 1].url}
            className="inline-flex items-center border-t-2 border-transparent pl-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
          >
            Suivant &rarr;
          </Link>
        )}
      </div>
    </nav>
  );
}
