import * as React from "react"
import { Label as LabelPrimitive } from "radix-ui"

import { cn } from "@fp/admin/lib/utils"

function Label({
  className,
  ...props
}: React.ComponentProps<typeof LabelPrimitive.Root>) {
  return (
    <LabelPrimitive.Root
      data-slot="label"
      className={cn(
        "fp-:flex fp-:items-center fp-:gap-2 fp-:text-sm fp-:leading-none fp-:font-medium fp-:select-none fp-:group-data-[disabled=true]:pointer-events-none fp-:group-data-[disabled=true]:opacity-50 fp-:peer-disabled:cursor-not-allowed fp-:peer-disabled:opacity-50",
        className
      )}
      {...props}
    />
  )
}

export { Label }
