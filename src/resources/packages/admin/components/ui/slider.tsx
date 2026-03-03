import * as React from "react"
import { Slider as SliderPrimitive } from "radix-ui"

import { cn } from "@fp/admin/lib/utils"

function Slider({
  className,
  defaultValue,
  value,
  min = 0,
  max = 100,
  ...props
}: React.ComponentProps<typeof SliderPrimitive.Root>) {
  const _values = React.useMemo(
    () =>
      Array.isArray(value)
        ? value
        : Array.isArray(defaultValue)
          ? defaultValue
          : [min, max],
    [value, defaultValue, min, max]
  )

  return (
    <SliderPrimitive.Root
      data-slot="slider"
      defaultValue={defaultValue}
      value={value}
      min={min}
      max={max}
      className={cn(
        "fp-:relative fp-:flex fp-:w-full fp-:touch-none fp-:items-center fp-:select-none fp-:data-[disabled]:opacity-50 fp-:data-[orientation=vertical]:h-full fp-:data-[orientation=vertical]:min-h-44 fp-:data-[orientation=vertical]:w-auto fp-:data-[orientation=vertical]:flex-col",
        className
      )}
      {...props}
    >
      <SliderPrimitive.Track
        data-slot="slider-track"
        className={cn(
          "fp-:relative fp-:grow fp-:overflow-hidden fp-:rounded-full fp-:bg-muted fp-:data-[orientation=horizontal]:h-1.5 fp-:data-[orientation=horizontal]:w-full fp-:data-[orientation=vertical]:h-full fp-:data-[orientation=vertical]:w-1.5"
        )}
      >
        <SliderPrimitive.Range
          data-slot="slider-range"
          className={cn(
            "fp-:absolute fp-:bg-primary fp-:data-[orientation=horizontal]:h-full fp-:data-[orientation=vertical]:w-full"
          )}
        />
      </SliderPrimitive.Track>
      {Array.from({ length: _values.length }, (_, index) => (
        <SliderPrimitive.Thumb
          data-slot="slider-thumb"
          key={index}
          className="fp-:block fp-:size-4 fp-:shrink-0 fp-:rounded-full fp-:border fp-:border-primary fp-:bg-white fp-:shadow-sm fp-:ring-ring/50 fp-:transition-[color,box-shadow] fp-:hover:ring-4 fp-:focus-visible:ring-4 fp-:focus-visible:outline-hidden fp-:disabled:pointer-events-none fp-:disabled:opacity-50"
        />
      ))}
    </SliderPrimitive.Root>
  )
}

export { Slider }
