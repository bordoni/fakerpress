import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@fp/admin/lib/utils"

const alertVariants = cva(
  "fp:relative fp:grid fp:w-full fp:grid-cols-[0_1fr] fp:items-start fp:gap-y-0.5 fp:rounded-lg fp:border fp:px-4 fp:py-3 fp:text-sm fp:has-[>svg]:grid-cols-[calc(var(--spacing)*4)_1fr] fp:has-[>svg]:gap-x-3 fp:[&>svg]:size-4 fp:[&>svg]:translate-y-0.5 fp:[&>svg]:text-current",
  {
    variants: {
      variant: {
        default: "fp:bg-card fp:text-card-foreground",
        destructive:
          "fp:bg-card fp:text-destructive fp:*:data-[slot=alert-description]:text-destructive/90 fp:[&>svg]:text-current",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
)

function Alert({
  className,
  variant,
  ...props
}: React.ComponentProps<"div"> & VariantProps<typeof alertVariants>) {
  return (
    <div
      data-slot="alert"
      role="alert"
      className={cn(alertVariants({ variant }), className)}
      {...props}
    />
  )
}

function AlertTitle({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="alert-title"
      className={cn(
        "fp:col-start-2 fp:line-clamp-1 fp:min-h-4 fp:font-medium fp:tracking-tight",
        className
      )}
      {...props}
    />
  )
}

function AlertDescription({
  className,
  ...props
}: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="alert-description"
      className={cn(
        "fp:col-start-2 fp:grid fp:justify-items-start fp:gap-1 fp:text-sm fp:text-muted-foreground fp:[&_p]:leading-relaxed",
        className
      )}
      {...props}
    />
  )
}

export { Alert, AlertTitle, AlertDescription }
