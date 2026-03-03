import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"
import { Slot } from "radix-ui"

import { cn } from "@fp/admin/lib/utils"

const badgeVariants = cva(
  "fp-:inline-flex fp-:w-fit fp-:shrink-0 fp-:items-center fp-:justify-center fp-:gap-1 fp-:overflow-hidden fp-:rounded-full fp-:border fp-:border-transparent fp-:px-2 fp-:py-0.5 fp-:text-xs fp-:font-medium fp-:whitespace-nowrap fp-:transition-[color,box-shadow] fp-:focus-visible:border-ring fp-:focus-visible:ring-[3px] fp-:focus-visible:ring-ring/50 fp-:aria-invalid:border-destructive fp-:aria-invalid:ring-destructive/20 fp-:dark:aria-invalid:ring-destructive/40 fp-:[&>svg]:pointer-events-none fp-:[&>svg]:size-3",
  {
    variants: {
      variant: {
        default: "fp-:bg-primary fp-:text-primary-foreground fp-:[a&]:hover:bg-primary/90",
        secondary:
          "fp-:bg-secondary fp-:text-secondary-foreground fp-:[a&]:hover:bg-secondary/90",
        destructive:
          "fp-:bg-destructive fp-:text-white fp-:focus-visible:ring-destructive/20 fp-:dark:bg-destructive/60 fp-:dark:focus-visible:ring-destructive/40 fp-:[a&]:hover:bg-destructive/90",
        outline:
          "fp-:border-border fp-:text-foreground fp-:[a&]:hover:bg-accent fp-:[a&]:hover:text-accent-foreground",
        ghost: "fp-:[a&]:hover:bg-accent fp-:[a&]:hover:text-accent-foreground",
        link: "fp-:text-primary fp-:underline-offset-4 fp-:[a&]:hover:underline",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
)

function Badge({
  className,
  variant = "default",
  asChild = false,
  ...props
}: React.ComponentProps<"span"> &
  VariantProps<typeof badgeVariants> & { asChild?: boolean }) {
  const Comp = asChild ? Slot.Root : "span"

  return (
    <Comp
      data-slot="badge"
      data-variant={variant}
      className={cn(badgeVariants({ variant }), className)}
      {...props}
    />
  )
}

export { Badge, badgeVariants }
