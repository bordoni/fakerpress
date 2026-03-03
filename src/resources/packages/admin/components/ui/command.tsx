import * as React from "react"
import { Command as CommandPrimitive } from "cmdk"
import { SearchIcon } from "lucide-react"

import { cn } from "@fp/admin/lib/utils"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from "@fp/admin/components/ui/dialog"

function Command({
  className,
  ...props
}: React.ComponentProps<typeof CommandPrimitive>) {
  return (
    <CommandPrimitive
      data-slot="command"
      className={cn(
        "fp-:flex fp-:h-full fp-:w-full fp-:flex-col fp-:overflow-hidden fp-:rounded-md fp-:bg-popover fp-:text-popover-foreground",
        className
      )}
      {...props}
    />
  )
}

function CommandDialog({
  title = "Command Palette",
  description = "Search for a command to run...",
  children,
  className,
  showCloseButton = true,
  ...props
}: React.ComponentProps<typeof Dialog> & {
  title?: string
  description?: string
  className?: string
  showCloseButton?: boolean
}) {
  return (
    <Dialog {...props}>
      <DialogHeader className="fp-:sr-only">
        <DialogTitle>{title}</DialogTitle>
        <DialogDescription>{description}</DialogDescription>
      </DialogHeader>
      <DialogContent
        className={cn("fp-:overflow-hidden fp-:p-0", className)}
        showCloseButton={showCloseButton}
      >
        <Command className="fp-:**:data-[slot=command-input-wrapper]:h-12 fp-:[&_[cmdk-group-heading]]:px-2 fp-:[&_[cmdk-group-heading]]:font-medium fp-:[&_[cmdk-group-heading]]:text-muted-foreground fp-:[&_[cmdk-group]]:px-2 fp-:[&_[cmdk-group]:not([hidden])_~[cmdk-group]]:pt-0 fp-:[&_[cmdk-input-wrapper]_svg]:h-5 fp-:[&_[cmdk-input-wrapper]_svg]:w-5 fp-:[&_[cmdk-input]]:h-12 fp-:[&_[cmdk-item]]:px-2 fp-:[&_[cmdk-item]]:py-3 fp-:[&_[cmdk-item]_svg]:h-5 fp-:[&_[cmdk-item]_svg]:w-5">
          {children}
        </Command>
      </DialogContent>
    </Dialog>
  )
}

function CommandInput({
  className,
  ...props
}: React.ComponentProps<typeof CommandPrimitive.Input>) {
  return (
    <div
      data-slot="command-input-wrapper"
      className="fp-:flex fp-:h-9 fp-:items-center fp-:gap-2 fp-:border-b fp-:px-3"
    >
      <SearchIcon className="fp-:size-4 fp-:shrink-0 fp-:opacity-50" />
      <CommandPrimitive.Input
        data-slot="command-input"
        className={cn(
          "fp-:flex fp-:h-10 fp-:w-full fp-:rounded-md fp-:bg-transparent fp-:py-3 fp-:text-sm fp-:outline-hidden fp-:placeholder:text-muted-foreground fp-:disabled:cursor-not-allowed fp-:disabled:opacity-50",
          className
        )}
        {...props}
      />
    </div>
  )
}

function CommandList({
  className,
  ...props
}: React.ComponentProps<typeof CommandPrimitive.List>) {
  return (
    <CommandPrimitive.List
      data-slot="command-list"
      className={cn(
        "fp-:max-h-[300px] fp-:scroll-py-1 fp-:overflow-x-hidden fp-:overflow-y-auto",
        className
      )}
      {...props}
    />
  )
}

function CommandEmpty({
  ...props
}: React.ComponentProps<typeof CommandPrimitive.Empty>) {
  return (
    <CommandPrimitive.Empty
      data-slot="command-empty"
      className="fp-:py-6 fp-:text-center fp-:text-sm"
      {...props}
    />
  )
}

function CommandGroup({
  className,
  ...props
}: React.ComponentProps<typeof CommandPrimitive.Group>) {
  return (
    <CommandPrimitive.Group
      data-slot="command-group"
      className={cn(
        "fp-:overflow-hidden fp-:p-1 fp-:text-foreground fp-:[&_[cmdk-group-heading]]:px-2 fp-:[&_[cmdk-group-heading]]:py-1.5 fp-:[&_[cmdk-group-heading]]:text-xs fp-:[&_[cmdk-group-heading]]:font-medium fp-:[&_[cmdk-group-heading]]:text-muted-foreground",
        className
      )}
      {...props}
    />
  )
}

function CommandSeparator({
  className,
  ...props
}: React.ComponentProps<typeof CommandPrimitive.Separator>) {
  return (
    <CommandPrimitive.Separator
      data-slot="command-separator"
      className={cn("fp-:-mx-1 fp-:h-px fp-:bg-border", className)}
      {...props}
    />
  )
}

function CommandItem({
  className,
  ...props
}: React.ComponentProps<typeof CommandPrimitive.Item>) {
  return (
    <CommandPrimitive.Item
      data-slot="command-item"
      className={cn(
        "fp-:relative fp-:flex fp-:cursor-default fp-:items-center fp-:gap-2 fp-:rounded-sm fp-:px-2 fp-:py-1.5 fp-:text-sm fp-:outline-hidden fp-:select-none fp-:data-[disabled=true]:pointer-events-none fp-:data-[disabled=true]:opacity-50 fp-:data-[selected=true]:bg-accent fp-:data-[selected=true]:text-accent-foreground fp-:[&_svg]:pointer-events-none fp-:[&_svg]:shrink-0 fp-:[&_svg:not([class*=size-])]:size-4 fp-:[&_svg:not([class*=text-])]:text-muted-foreground",
        className
      )}
      {...props}
    />
  )
}

function CommandShortcut({
  className,
  ...props
}: React.ComponentProps<"span">) {
  return (
    <span
      data-slot="command-shortcut"
      className={cn(
        "fp-:ml-auto fp-:text-xs fp-:tracking-widest fp-:text-muted-foreground",
        className
      )}
      {...props}
    />
  )
}

export {
  Command,
  CommandDialog,
  CommandInput,
  CommandList,
  CommandEmpty,
  CommandGroup,
  CommandItem,
  CommandShortcut,
  CommandSeparator,
}
