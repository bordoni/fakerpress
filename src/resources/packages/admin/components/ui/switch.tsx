"use client"

import * as React from "react"
import { Switch as SwitchPrimitive } from "radix-ui"

import { cn } from "@fp/admin/lib/utils"

function Switch({
  className,
  size = "default",
  ...props
}: React.ComponentProps<typeof SwitchPrimitive.Root> & {
  size?: "sm" | "default"
}) {
  return (
    <SwitchPrimitive.Root
      data-slot="switch"
      data-size={size}
      className={cn(
        "fp-:peer fp-:group/switch fp-:inline-flex fp-:shrink-0 fp-:items-center fp-:rounded-full fp-:border fp-:border-transparent fp-:shadow-xs fp-:transition-all fp-:outline-none fp-:focus-visible:border-ring fp-:focus-visible:ring-[3px] fp-:focus-visible:ring-ring/50 fp-:disabled:cursor-not-allowed fp-:disabled:opacity-50 fp-:data-[size=default]:h-[1.15rem] fp-:data-[size=default]:w-8 fp-:data-[size=sm]:h-3.5 fp-:data-[size=sm]:w-6 fp-:data-[state=checked]:bg-primary fp-:data-[state=unchecked]:bg-input fp-:dark:data-[state=unchecked]:bg-input/80",
        className
      )}
      {...props}
    >
      <SwitchPrimitive.Thumb
        data-slot="switch-thumb"
        className={cn(
          "fp-:pointer-events-none fp-:block fp-:rounded-full fp-:bg-background fp-:ring-0 fp-:transition-transform fp-:group-data-[size=default]/switch:size-4 fp-:group-data-[size=sm]/switch:size-3 fp-:data-[state=checked]:translate-x-[calc(100%-2px)] fp-:data-[state=unchecked]:translate-x-0 fp-:dark:data-[state=checked]:bg-primary-foreground fp-:dark:data-[state=unchecked]:bg-foreground"
        )}
      />
    </SwitchPrimitive.Root>
  )
}

export { Switch }
