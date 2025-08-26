import React from "react"

export function FormField({
  label,
  htmlFor,
  children,
  error,
  hint,
}: {
  label: string
  htmlFor: string
  children: React.ReactNode
  error?: string | string[]
  hint?: string
}) {
  const err = Array.isArray(error) ? error.join(" ") : error
  const describedBy = err ? `${htmlFor}-error` : hint ? `${htmlFor}-hint` : undefined
  return (
    <div className="grid gap-1">
      <label htmlFor={htmlFor} className="text-sm text-gray-700">{label}</label>
      {React.isValidElement(children)
        ? React.cloneElement(children as any, { "aria-invalid": !!err, "aria-describedby": describedBy })
        : children}
      {err ? (
        <p id={`${htmlFor}-error`} className="text-sm text-red-600">{err}</p>
      ) : hint ? (
        <p id={`${htmlFor}-hint`} className="text-xs text-gray-500">{hint}</p>
      ) : null}
    </div>
  )
}
