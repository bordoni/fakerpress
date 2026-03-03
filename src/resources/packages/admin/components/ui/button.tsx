import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"
import { Slot } from "radix-ui"

import { cn } from "@fp/admin/lib/utils"

const buttonVariants = cva(
  "fp:inline-flex fp:shrink-0 fp:items-center fp:justify-center fp:gap-2 fp:rounded-md fp:text-sm fp:font-medium fp:whitespace-nowrap fp:transition-all fp:outline-none fp:focus-visible:border-ring fp:focus-visible:ring-[3px] fp:focus-visible:ring-ring/50 fp:disabled:pointer-events-none fp:disabled:opacity-50 fp:aria-invalid:border-destructive fp:aria-invalid:ring-destructive/20 fp:dark:aria-invalid:ring-destructive/40 fp:[&_svg]:pointer-events-none fp:[&_svg]:shrink-0 fp:[&_svg:not([class*=size-])]:size-4",
  {
    variants: {
      variant: {
        default: "fp:bg-primary fp:text-primary-foreground fp:hover:bg-primary/90",
        destructive:
          "fp:bg-destructive fp:text-white fp:hover:bg-destructive/90 fp:focus-visible:ring-destructive/20 fp:dark:bg-destructive/60 fp:dark:focus-visible:ring-destructive/40",
        outline:
          "fp:border fp:bg-background fp:shadow-xs fp:hover:bg-accent fp:hover:text-accent-foreground fp:dark:border-input fp:dark:bg-input/30 fp:dark:hover:bg-input/50",
        secondary:
          "fp:bg-secondary fp:text-secondary-foreground fp:hover:bg-secondary/80",
        ghost:
          "fp:hover:bg-accent fp:hover:text-accent-foreground fp:dark:hover:bg-accent/50",
        link: "fp:text-primary fp:underline-offset-4 fp:hover:underline",
      },
      size: {
        default: "fp:h-9 fp:px-4 fp:py-2 fp:has-[>svg]:px-3",
        xs: "fp:h-6 fp:gap-1 fp:rounded-md fp:px-2 fp:text-xs fp:has-[>svg]:px-1.5 fp:[&_svg:not([class*=size-])]:size-3",
        sm: "fp:h-8 fp:gap-1.5 fp:rounded-md fp:px-3 fp:has-[>svg]:px-2.5",
        lg: "fp:h-10 fp:rounded-md fp:px-6 fp:has-[>svg]:px-4",
        icon: "fp:size-9",
        "icon-xs": "fp:size-6 fp:rounded-md fp:[&_svg:not([class*=size-])]:size-3",
        "icon-sm": "fp:size-8",
        "icon-lg": "fp:size-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)

function Button({
  className,
  variant = "default",
  size = "default",
  asChild = false,
  ...props
}: React.ComponentProps<"button"> &
  VariantProps<typeof buttonVariants> & {
    asChild?: boolean
  }) {
  const Comp = asChild ? Slot.Root : "button"

  return (
    <Comp
      data-slot="button"
      data-variant={variant}
      data-size={size}
      className={cn(buttonVariants({ variant, size, className }))}
      {...props}
    />
  )
}

export { Button, buttonVariants }
