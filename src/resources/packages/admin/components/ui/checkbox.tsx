"use client"

import * as React from "react"
import { CheckIcon } from "lucide-react"
import { Checkbox as CheckboxPrimitive } from "radix-ui"

import { cn } from "@fp/admin/lib/utils"

function Checkbox({
  className,
  ...props
}: React.ComponentProps<typeof CheckboxPrimitive.Root>) {
  return (
    <CheckboxPrimitive.Root
      data-slot="checkbox"
      className={cn(
        "fp:peer fp:size-4 fp:shrink-0 fp:rounded-[4px] fp:border fp:border-input fp:shadow-xs fp:transition-shadow fp:outline-none fp:focus-visible:border-ring fp:focus-visible:ring-[3px] fp:focus-visible:ring-ring/50 fp:disabled:cursor-not-allowed fp:disabled:opacity-50 fp:aria-invalid:border-destructive fp:aria-invalid:ring-destructive/20 fp:data-[state=checked]:border-primary fp:data-[state=checked]:bg-primary fp:data-[state=checked]:text-primary-foreground fp:dark:bg-input/30 fp:dark:aria-invalid:ring-destructive/40 fp:dark:data-[state=checked]:bg-primary",
        className
      )}
      {...props}
    >
      <CheckboxPrimitive.Indicator
        data-slot="checkbox-indicator"
        className="fp:grid fp:place-content-center fp:text-current fp:transition-none"
      >
        <CheckIcon className="fp:size-3.5" />
      </CheckboxPrimitive.Indicator>
    </CheckboxPrimitive.Root>
  )
}

export { Checkbox }
