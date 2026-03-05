import * as React from "react"
import { Popover as PopoverPrimitive } from "radix-ui"

import { cn } from "@fp/admin/lib/utils"

function Popover({
  ...props
}: React.ComponentProps<typeof PopoverPrimitive.Root>) {
  return <PopoverPrimitive.Root data-slot="popover" {...props} />
}

function PopoverTrigger({
  ...props
}: React.ComponentProps<typeof PopoverPrimitive.Trigger>) {
  return <PopoverPrimitive.Trigger data-slot="popover-trigger" {...props} />
}

function PopoverContent({
  className,
  align = "center",
  sideOffset = 4,
  ...props
}: React.ComponentProps<typeof PopoverPrimitive.Content>) {
  const container =
    typeof document !== 'undefined'
      ? document.getElementById( 'fakerpress-react-root' )
      : undefined;
  return (
    <PopoverPrimitive.Portal container={ container ?? undefined }>
      <PopoverPrimitive.Content
        data-slot="popover-content"
        align={align}
        sideOffset={sideOffset}
        className={cn(
          "fp:z-50 fp:w-72 fp:origin-(--radix-popover-content-transform-origin) fp:rounded-md fp:border fp:bg-popover fp:p-4 fp:text-popover-foreground fp:shadow-md fp:outline-hidden fp:data-[side=bottom]:slide-in-from-top-2 fp:data-[side=left]:slide-in-from-right-2 fp:data-[side=right]:slide-in-from-left-2 fp:data-[side=top]:slide-in-from-bottom-2 fp:data-[state=closed]:animate-out fp:data-[state=closed]:fade-out-0 fp:data-[state=closed]:zoom-out-95 fp:data-[state=open]:animate-in fp:data-[state=open]:fade-in-0 fp:data-[state=open]:zoom-in-95",
          className
        )}
        {...props}
      />
    </PopoverPrimitive.Portal>
  )
}

function PopoverAnchor({
  ...props
}: React.ComponentProps<typeof PopoverPrimitive.Anchor>) {
  return <PopoverPrimitive.Anchor data-slot="popover-anchor" {...props} />
}

function PopoverHeader({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="popover-header"
      className={cn("fp:flex fp:flex-col fp:gap-1 fp:text-sm", className)}
      {...props}
    />
  )
}

function PopoverTitle({ className, ...props }: React.ComponentProps<"h2">) {
  return (
    <div
      data-slot="popover-title"
      className={cn("fp:font-medium", className)}
      {...props}
    />
  )
}

function PopoverDescription({
  className,
  ...props
}: React.ComponentProps<"p">) {
  return (
    <p
      data-slot="popover-description"
      className={cn("fp:text-muted-foreground", className)}
      {...props}
    />
  )
}

export {
  Popover,
  PopoverTrigger,
  PopoverContent,
  PopoverAnchor,
  PopoverHeader,
  PopoverTitle,
  PopoverDescription,
}
