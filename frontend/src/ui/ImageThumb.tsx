import React from "react"

export default function ImageThumb({ images }: { images?: string[] }) {
  const arr = Array.isArray(images) ? images : []
  const [i, setI] = React.useState(0)
  if (arr.length === 0) return <div className="w-16 h-10 bg-gray-200 rounded" />

  const go = (d: number) => setI((p) => (p + d + arr.length) % arr.length)

  return (
    <div className="relative w-20 h-12 overflow-hidden rounded border bg-white">
      <img src={arr[i]} alt="" className="w-full h-full object-cover" />
      {arr.length > 1 && (
        <>
          <button onClick={() => go(-1)} className="absolute left-0 top-0 bottom-0 px-1 text-xs bg-black/30 text-white">‹</button>
          <button onClick={() => go(+1)} className="absolute right-0 top-0 bottom-0 px-1 text-xs bg-black/30 text-white">›</button>
        </>
      )}
    </div>
  )
}
