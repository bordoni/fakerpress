import * as React from "react"
import { CheckIcon, ChevronDownIcon, ChevronUpIcon } from "lucide-react"
import { Select as SelectPrimitive } from "radix-ui"

import { cn } from "@fp/admin/lib/utils"

function Select({
  ...props
}: React.ComponentProps<typeof SelectPrimitive.Root>) {
  return <SelectPrimitive.Root data-slot="select" {...props} />
}

function SelectGroup({
  ...props
}: React.ComponentProps<typeof SelectPrimitive.Group>) {
  return <SelectPrimitive.Group data-slot="select-group" {...props} />
}

function SelectValue({
  ...props
}: React.ComponentProps<typeof SelectPrimitive.Value>) {
  return <SelectPrimitive.Value data-slot="select-value" {...props} />
}

function SelectTrigger({
  className,
  size = "default",
  children,
  ...props
}: React.ComponentProps<typeof SelectPrimitive.Trigger> & {
  size?: "sm" | "default"
}) {
  return (
    <SelectPrimitive.Trigger
      data-slot="select-trigger"
      data-size={size}
      className={cn(
        "fp:flex fp:w-fit fp:items-center fp:justify-between fp:gap-2 fp:rounded-md fp:border fp:border-input fp:bg-transparent fp:px-3 fp:py-2 fp:text-sm fp:whitespace-nowrap fp:shadow-xs fp:transition-[color,box-shadow] fp:outline-none fp:focus-visible:border-ring fp:focus-visible:ring-[3px] fp:focus-visible:ring-ring/50 fp:disabled:cursor-not-allowed fp:disabled:opacity-50 fp:aria-invalid:border-destructive fp:aria-invalid:ring-destructive/20 fp:data-[placeholder]:text-muted-foreground fp:data-[size=default]:h-9 fp:data-[size=sm]:h-8 fp:*:data-[slot=select-value]:line-clamp-1 fp:*:data-[slot=select-value]:flex fp:*:data-[slot=select-value]:items-center fp:*:data-[slot=select-value]:gap-2 fp:dark:bg-input/30 fp:dark:hover:bg-input/50 fp:dark:aria-invalid:ring-destructive/40 fp:[&_svg]:pointer-events-none fp:[&_svg]:shrink-0 fp:[&_svg:not([class*=size-])]:size-4 fp:[&_svg:not([class*=text-])]:text-muted-foreground",
        className
      )}
      {...props}
    >
      {children}
      <SelectPrimitive.Icon asChild>
        <ChevronDownIcon className="fp:size-4 fp:opacity-50" />
      </SelectPrimitive.Icon>
    </SelectPrimitive.Trigger>
  )
}

function SelectContent({
  className,
  children,
  position = "item-aligned",
  align = "center",
  ...props
}: React.ComponentProps<typeof SelectPrimitive.Content>) {
  return (
    <SelectPrimitive.Portal>
      <SelectPrimitive.Content
        data-slot="select-content"
        className={cn(
          "fp:relative fp:z-50 fp:max-h-(--radix-select-content-available-height) fp:min-w-[8rem] fp:origin-(--radix-select-content-transform-origin) fp:overflow-x-hidden fp:overflow-y-auto fp:rounded-md fp:border fp:bg-popover fp:text-popover-foreground fp:shadow-md fp:data-[side=bottom]:slide-in-from-top-2 fp:data-[side=left]:slide-in-from-right-2 fp:data-[side=right]:slide-in-from-left-2 fp:data-[side=top]:slide-in-from-bottom-2 fp:data-[state=closed]:animate-out fp:data-[state=closed]:fade-out-0 fp:data-[state=closed]:zoom-out-95 fp:data-[state=open]:animate-in fp:data-[state=open]:fade-in-0 fp:data-[state=open]:zoom-in-95",
          position === "popper" &&
            "fp:data-[side=bottom]:translate-y-1 fp:data-[side=left]:-translate-x-1 fp:data-[side=right]:translate-x-1 fp:data-[side=top]:-translate-y-1",
          className
        )}
        position={position}
        align={align}
        {...props}
      >
        <SelectScrollUpButton />
        <SelectPrimitive.Viewport
          className={cn(
            "fp:p-1",
            position === "popper" &&
              "fp:h-[var(--radix-select-trigger-height)] fp:w-full fp:min-w-[var(--radix-select-trigger-width)] fp:scroll-my-1"
          )}
        >
          {children}
        </SelectPrimitive.Viewport>
        <SelectScrollDownButton />
      </SelectPrimitive.Content>
    </SelectPrimitive.Portal>
  )
}

function SelectLabel({
  className,
  ...props
}: React.ComponentProps<typeof SelectPrimitive.Label>) {
  return (
    <SelectPrimitive.Label
      data-slot="select-label"
      className={cn("fp:px-2 fp:py-1.5 fp:text-xs fp:text-muted-foreground", className)}
      {...props}
    />
  )
}

function SelectItem({
  className,
  children,
  ...props
}: React.ComponentProps<typeof SelectPrimitive.Item>) {
  return (
    <SelectPrimitive.Item
      data-slot="select-item"
      className={cn(
        "fp:relative fp:flex fp:w-full fp:cursor-default fp:items-center fp:gap-2 fp:rounded-sm fp:py-1.5 fp:pr-8 fp:pl-2 fp:text-sm fp:outline-hidden fp:select-none fp:focus:bg-accent fp:focus:text-accent-foreground fp:data-[disabled]:pointer-events-none fp:data-[disabled]:opacity-50 fp:[&_svg]:pointer-events-none fp:[&_svg]:shrink-0 fp:[&_svg:not([class*=size-])]:size-4 fp:[&_svg:not([class*=text-])]:text-muted-foreground fp:*:[span]:last:flex fp:*:[span]:last:items-center fp:*:[span]:last:gap-2",
        className
      )}
      {...props}
    >
      <span
        data-slot="select-item-indicator"
        className="fp:absolute fp:right-2 fp:flex fp:size-3.5 fp:items-center fp:justify-center"
      >
        <SelectPrimitive.ItemIndicator>
          <CheckIcon className="fp:size-4" />
        </SelectPrimitive.ItemIndicator>
      </span>
      <SelectPrimitive.ItemText>{children}</SelectPrimitive.ItemText>
    </SelectPrimitive.Item>
  )
}

function SelectSeparator({
  className,
  ...props
}: React.ComponentProps<typeof SelectPrimitive.Separator>) {
  return (
    <SelectPrimitive.Separator
      data-slot="select-separator"
      className={cn("fp:pointer-events-none fp:-mx-1 fp:my-1 fp:h-px fp:bg-border", className)}
      {...props}
    />
  )
}

function SelectScrollUpButton({
  className,
  ...props
}: React.ComponentProps<typeof SelectPrimitive.ScrollUpButton>) {
  return (
    <SelectPrimitive.ScrollUpButton
      data-slot="select-scroll-up-button"
      className={cn(
        "fp:flex fp:cursor-default fp:items-center fp:justify-center fp:py-1",
        className
      )}
      {...props}
    >
      <ChevronUpIcon className="fp:size-4" />
    </SelectPrimitive.ScrollUpButton>
  )
}

function SelectScrollDownButton({
  className,
  ...props
}: React.ComponentProps<typeof SelectPrimitive.ScrollDownButton>) {
  return (
    <SelectPrimitive.ScrollDownButton
      data-slot="select-scroll-down-button"
      className={cn(
        "fp:flex fp:cursor-default fp:items-center fp:justify-center fp:py-1",
        className
      )}
      {...props}
    >
      <ChevronDownIcon className="fp:size-4" />
    </SelectPrimitive.ScrollDownButton>
  )
}

export {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectScrollDownButton,
  SelectScrollUpButton,
  SelectSeparator,
  SelectTrigger,
  SelectValue,
}
