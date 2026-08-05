import { StarIcon } from '@heroicons/react/20/solid';

export default function RatingStars({ rating = 0, total = null, size = 'sm' }) {
    const value = Number(rating || 0);
    const rounded = Math.round(value);
    const iconSize = size === 'lg' ? 'h-5 w-5' : 'h-4 w-4';

    return (
        <div className="inline-flex items-center gap-2">
            <div className="flex items-center gap-0.5" aria-label={`${value.toFixed(1)} out of 5 stars`}>
                {[1, 2, 3, 4, 5].map((star) => (
                    <StarIcon key={star} className={`${iconSize} ${star <= rounded ? 'text-amber-400' : 'text-slate-200'}`} />
                ))}
            </div>
            <span className="text-xs font-medium text-slate-600">
                {value.toFixed(1)}/5{total !== null ? ` (${total})` : ''}
            </span>
        </div>
    );
}
