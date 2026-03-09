import * as React from "react"
import { CheckIcon, ChevronRightIcon, CircleIcon } from "lucide-react"
import { DropdownMenu as DropdownMenuPrimitive } from "radix-ui"

import { cn } from "@fp/admin/lib/utils"

function DropdownMenu({
  ...props
}: React.ComponentProps<typeof DropdownMenuPrimitive.Root>) {
  return <DropdownMenuPrimitive.Root data-slot="dropdown-menu" {...props} />
}

function DropdownMenuPortal({
  ...props
}: React.ComponentProps<typeof DropdownMenuPrimitive.Portal>) {
  return (
    <DropdownMenuPrimitive.Portal data-slot="dropdown-menu-portal" {...props} />
  )
}

function DropdownMenuTrigger({
  ...props
}: React.ComponentProps<typeof DropdownMenuPrimitive.Trigger>) {
  return (
    <DropdownMenuPrimitive.Trigger
      data-slot="dropdown-menu-trigger"
      {...props}
    />
  )
}

function DropdownMenuContent({
  className,
  sideOffset = 4,
  ...props
}: React.ComponentProps<typeof DropdownMenuPrimitive.Content>) {
  const container =
    typeof document !== 'undefined'
      ? document.getElementById( 'fakerpress-react-root' )
      : undefined;
  return (
    <DropdownMenuPrimitive.Portal container={ container ?? undefined }>
      <DropdownMenuPrimitive.Content
        data-slot="dropdown-menu-content"
        sideOffset={sideOffset}
        className={cn(
          "fp:z-50 fp:max-h-(--radix-dropdown-menu-content-available-height) fp:min-w-[8rem] fp:origin-(--radix-dropdown-menu-content-transform-origin) fp:overflow-x-hidden fp:overflow-y-auto fp:rounded-md fp:border fp:bg-popover fp:p-1 fp:text-popover-foreground fp:shadow-md fp:data-[side=bottom]:slide-in-from-top-2 fp:data-[side=left]:slide-in-from-right-2 fp:data-[side=right]:slide-in-from-left-2 fp:data-[side=top]:slide-in-from-bottom-2 fp:data-[state=closed]:animate-out fp:data-[state=closed]:fade-out-0 fp:data-[state=closed]:zoom-out-95 fp:data-[state=open]:animate-in fp:data-[state=open]:fade-in-0 fp:data-[state=open]:zoom-in-95",
          className
        )}
        {...props}
      />
    </DropdownMenuPrimitive.Portal>
  )
}

function DropdownMenuGroup({
  ...props
}: React.ComponentProps<typeof DropdownMenuPrimitive.Group>) {
  return (
    <DropdownMenuPrimitive.Group data-slot="dropdown-menu-group" {...props} />
  )
}

function DropdownMenuItem({
  className,
  inset,
  variant = "default",
  ...props
}: React.ComponentProps<typeof DropdownMenuPrimitive.Item> & {
  inset?: boolean
  variant?: "default" | "destructive"
}) {
  return (
    <DropdownMenuPrimitive.Item
      data-slot="dropdown-menu-item"
      data-inset={inset}
      data-variant={variant}
      className={cn(
        "fp:relative fp:flex fp:cursor-default fp:items-center fp:gap-2 fp:rounded-sm fp:px-2 fp:py-1.5 fp:text-sm fp:outline-hidden fp:select-none fp:focus:bg-accent fp:focus:text-accent-foreground fp:data-[disabled]:pointer-events-none fp:data-[disabled]:opacity-50 fp:data-[inset]:pl-8 fp:data-[variant=destructive]:text-destructive fp:data-[variant=destructive]:focus:bg-destructive/10 fp:data-[variant=destructive]:focus:text-destructive fp:dark:data-[variant=destructive]:focus:bg-destructive/20 fp:[&_svg]:pointer-events-none fp:[&_svg]:shrink-0 fp:[&_svg:not([class*=size-])]:size-4 fp:[&_svg:not([class*=text-])]:text-muted-foreground fp:data-[variant=destructive]:*:[svg]:text-destructive!",
        className
      )}
      {...props}
    />
  )
}

function DropdownMenuCheckboxItem({
  className,
  children,
  checked,
  ...props
}: React.ComponentProps<typeof DropdownMenuPrimitive.CheckboxItem>) {
  return (
    <DropdownMenuPrimitive.CheckboxItem
      data-slot="dropdown-menu-checkbox-item"
      className={cn(
        "fp:relative fp:flex fp:cursor-default fp:items-center fp:gap-2 fp:rounded-sm fp:py-1.5 fp:pr-2 fp:pl-8 fp:text-sm fp:outline-hidden fp:select-none fp:focus:bg-accent fp:focus:text-accent-foreground fp:data-[disabled]:pointer-events-none fp:data-[disabled]:opacity-50 fp:[&_svg]:pointer-events-none fp:[&_svg]:shrink-0 fp:[&_svg:not([class*=size-])]:size-4",
        className
      )}
      checked={checked}
      {...props}
    >
      <span className="fp:pointer-events-none fp:absolute fp:left-2 fp:flex fp:size-3.5 fp:items-center fp:justify-center">
        <DropdownMenuPrimitive.ItemIndicator>
          <CheckIcon className="fp:size-4" />
        </DropdownMenuPrimitive.ItemIndicator>
      </span>
      {children}
    </DropdownMenuPrimitive.CheckboxItem>
  )
}

function DropdownMenuRadioGroup({
  ...props
}: React.ComponentProps<typeof DropdownMenuPrimitive.RadioGroup>) {
  return (
    <DropdownMenuPrimitive.RadioGroup
      data-slot="dropdown-menu-radio-group"
      {...props}
    />
  )
}

function DropdownMenuRadioItem({
  className,
  children,
  ...props
}: React.ComponentProps<typeof DropdownMenuPrimitive.RadioItem>) {
  return (
    <DropdownMenuPrimitive.RadioItem
      data-slot="dropdown-menu-radio-item"
      className={cn(
        "fp:relative fp:flex fp:cursor-default fp:items-center fp:gap-2 fp:rounded-sm fp:py-1.5 fp:pr-2 fp:pl-8 fp:text-sm fp:outline-hidden fp:select-none fp:focus:bg-accent fp:focus:text-accent-foreground fp:data-[disabled]:pointer-events-none fp:data-[disabled]:opacity-50 fp:[&_svg]:pointer-events-none fp:[&_svg]:shrink-0 fp:[&_svg:not([class*=size-])]:size-4",
        className
      )}
      {...props}
    >
      <span className="fp:pointer-events-none fp:absolute fp:left-2 fp:flex fp:size-3.5 fp:items-center fp:justify-center">
        <DropdownMenuPrimitive.ItemIndicator>
          <CircleIcon className="fp:size-2 fp:fill-current" />
        </DropdownMenuPrimitive.ItemIndicator>
      </span>
      {children}
    </DropdownMenuPrimitive.RadioItem>
  )
}

function DropdownMenuLabel({
  className,
  inset,
  ...props
}: React.ComponentProps<typeof DropdownMenuPrimitive.Label> & {
  inset?: boolean
}) {
  return (
    <DropdownMenuPrimitive.Label
      data-slot="dropdown-menu-label"
      data-inset={inset}
      className={cn(
        "fp:px-2 fp:py-1.5 fp:text-sm fp:font-medium fp:data-[inset]:pl-8",
        className
      )}
      {...props}
    />
  )
}

function DropdownMenuSeparator({
  className,
  ...props
}: React.ComponentProps<typeof DropdownMenuPrimitive.Separator>) {
  return (
    <DropdownMenuPrimitive.Separator
      data-slot="dropdown-menu-separator"
      className={cn("fp:-mx-1 fp:my-1 fp:h-px fp:bg-border", className)}
      {...props}
    />
  )
}

function DropdownMenuShortcut({
  className,
  ...props
}: React.ComponentProps<"span">) {
  return (
    <span
      data-slot="dropdown-menu-shortcut"
      className={cn(
        "fp:ml-auto fp:text-xs fp:tracking-widest fp:text-muted-foreground",
        className
      )}
      {...props}
    />
  )
}

function DropdownMenuSub({
  ...props
}: React.ComponentProps<typeof DropdownMenuPrimitive.Sub>) {
  return <DropdownMenuPrimitive.Sub data-slot="dropdown-menu-sub" {...props} />
}

function DropdownMenuSubTrigger({
  className,
  inset,
  children,
  ...props
}: React.ComponentProps<typeof DropdownMenuPrimitive.SubTrigger> & {
  inset?: boolean
}) {
  return (
    <DropdownMenuPrimitive.SubTrigger
      data-slot="dropdown-menu-sub-trigger"
      data-inset={inset}
      className={cn(
        "fp:flex fp:cursor-default fp:items-center fp:gap-2 fp:rounded-sm fp:px-2 fp:py-1.5 fp:text-sm fp:outline-hidden fp:select-none fp:focus:bg-accent fp:focus:text-accent-foreground fp:data-[inset]:pl-8 fp:data-[state=open]:bg-accent fp:data-[state=open]:text-accent-foreground fp:[&_svg]:pointer-events-none fp:[&_svg]:shrink-0 fp:[&_svg:not([class*=size-])]:size-4 fp:[&_svg:not([class*=text-])]:text-muted-foreground",
        className
      )}
      {...props}
    >
      {children}
      <ChevronRightIcon className="fp:ml-auto fp:size-4" />
    </DropdownMenuPrimitive.SubTrigger>
  )
}

function DropdownMenuSubContent({
  className,
  ...props
}: React.ComponentProps<typeof DropdownMenuPrimitive.SubContent>) {
  return (
    <DropdownMenuPrimitive.SubContent
      data-slot="dropdown-menu-sub-content"
      className={cn(
        "fp:z-50 fp:min-w-[8rem] fp:origin-(--radix-dropdown-menu-content-transform-origin) fp:overflow-hidden fp:rounded-md fp:border fp:bg-popover fp:p-1 fp:text-popover-foreground fp:shadow-lg fp:data-[side=bottom]:slide-in-from-top-2 fp:data-[side=left]:slide-in-from-right-2 fp:data-[side=right]:slide-in-from-left-2 fp:data-[side=top]:slide-in-from-bottom-2 fp:data-[state=closed]:animate-out fp:data-[state=closed]:fade-out-0 fp:data-[state=closed]:zoom-out-95 fp:data-[state=open]:animate-in fp:data-[state=open]:fade-in-0 fp:data-[state=open]:zoom-in-95",
        className
      )}
      {...props}
    />
  )
}

export {
  DropdownMenu,
  DropdownMenuPortal,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuLabel,
  DropdownMenuItem,
  DropdownMenuCheckboxItem,
  DropdownMenuRadioGroup,
  DropdownMenuRadioItem,
  DropdownMenuSeparator,
  DropdownMenuShortcut,
  DropdownMenuSub,
  DropdownMenuSubTrigger,
  DropdownMenuSubContent,
}
