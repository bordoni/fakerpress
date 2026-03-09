import * as React from "react"

import { cn } from "@fp/admin/lib/utils"

function Input({ className, type, ...props }: React.ComponentProps<"input">) {
  return (
    <input
      type={type}
      data-slot="input"
      className={cn(
        "fp:h-8 fp:w-full fp:min-w-0 fp:rounded-md fp:border fp:border-input fp:bg-background fp:px-2 fp:py-1 fp:text-sm fp:shadow-xs fp:transition-[color,box-shadow] fp:outline-none fp:selection:bg-primary fp:selection:text-primary-foreground fp:file:inline-flex fp:file:h-7 fp:file:border-0 fp:file:bg-transparent fp:file:text-sm fp:file:font-medium fp:file:text-foreground fp:placeholder:text-muted-foreground fp:disabled:pointer-events-none fp:disabled:cursor-not-allowed fp:disabled:opacity-50 fp:dark:bg-input/30",
        "fp:focus-visible:border-ring fp:focus-visible:ring-[3px] fp:focus-visible:ring-ring/50",
        "fp:aria-invalid:border-destructive fp:aria-invalid:ring-destructive/20 fp:dark:aria-invalid:ring-destructive/40",
        className
      )}
      {...props}
    />
  )
}

export { Input }
