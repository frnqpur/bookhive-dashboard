import { forwardRef, useEffect, useRef } from 'react';

export default forwardRef(function TextInput({ type = 'text', className = '', isFocused = false, ...props }, ref) {
    const localRef = useRef();
    const input = ref || localRef;

    useEffect(() => {
        if (isFocused) {
            input.current?.focus();
        }
    }, [isFocused, input]);

    return (
        <input
            {...props}
            type={type}
            className={`rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-100 disabled:text-slate-500 ${className}`}
            ref={input}
        />
    );
});
