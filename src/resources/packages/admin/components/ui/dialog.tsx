"use client"

import * as React from "react"
import { XIcon } from "lucide-react"
import { Dialog as DialogPrimitive } from "radix-ui"

import { cn } from "@fp/admin/lib/utils"
import { Button } from "@fp/admin/components/ui/button"

function Dialog({
  ...props
}: React.ComponentProps<typeof DialogPrimitive.Root>) {
  return <DialogPrimitive.Root data-slot="dialog" {...props} />
}

function DialogTrigger({
  ...props
}: React.ComponentProps<typeof DialogPrimitive.Trigger>) {
  return <DialogPrimitive.Trigger data-slot="dialog-trigger" {...props} />
}

function DialogPortal({
  ...props
}: React.ComponentProps<typeof DialogPrimitive.Portal>) {
  return <DialogPrimitive.Portal data-slot="dialog-portal" {...props} />
}

function DialogClose({
  ...props
}: React.ComponentProps<typeof DialogPrimitive.Close>) {
  return <DialogPrimitive.Close data-slot="dialog-close" {...props} />
}

function DialogOverlay({
  className,
  ...props
}: React.ComponentProps<typeof DialogPrimitive.Overlay>) {
  return (
    <DialogPrimitive.Overlay
      data-slot="dialog-overlay"
      className={cn(
        "fp-:fixed fp-:inset-0 fp-:z-50 fp-:bg-black/50 fp-:data-[state=closed]:animate-out fp-:data-[state=closed]:fade-out-0 fp-:data-[state=open]:animate-in fp-:data-[state=open]:fade-in-0",
        className
      )}
      {...props}
    />
  )
}

function DialogContent({
  className,
  children,
  showCloseButton = true,
  ...props
}: React.ComponentProps<typeof DialogPrimitive.Content> & {
  showCloseButton?: boolean
}) {
  return (
    <DialogPortal data-slot="dialog-portal">
      <DialogOverlay />
      <DialogPrimitive.Content
        data-slot="dialog-content"
        className={cn(
          "fp-:fixed fp-:top-[50%] fp-:left-[50%] fp-:z-50 fp-:grid fp-:w-full fp-:max-w-[calc(100%-2rem)] fp-:translate-x-[-50%] fp-:translate-y-[-50%] fp-:gap-4 fp-:rounded-lg fp-:border fp-:bg-background fp-:p-6 fp-:shadow-lg fp-:duration-200 fp-:outline-none fp-:data-[state=closed]:animate-out fp-:data-[state=closed]:fade-out-0 fp-:data-[state=closed]:zoom-out-95 fp-:data-[state=open]:animate-in fp-:data-[state=open]:fade-in-0 fp-:data-[state=open]:zoom-in-95 fp-:sm:max-w-lg",
          className
        )}
        {...props}
      >
        {children}
        {showCloseButton && (
          <DialogPrimitive.Close
            data-slot="dialog-close"
            className="fp-:absolute fp-:top-4 fp-:right-4 fp-:rounded-xs fp-:opacity-70 fp-:ring-offset-background fp-:transition-opacity fp-:hover:opacity-100 fp-:focus:ring-2 fp-:focus:ring-ring fp-:focus:ring-offset-2 fp-:focus:outline-hidden fp-:disabled:pointer-events-none fp-:data-[state=open]:bg-accent fp-:data-[state=open]:text-muted-foreground fp-:[&_svg]:pointer-events-none fp-:[&_svg]:shrink-0 fp-:[&_svg:not([class*=size-])]:size-4"
          >
            <XIcon />
            <span className="fp-:sr-only">Close</span>
          </DialogPrimitive.Close>
        )}
      </DialogPrimitive.Content>
    </DialogPortal>
  )
}

function DialogHeader({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="dialog-header"
      className={cn("fp-:flex fp-:flex-col fp-:gap-2 fp-:text-center fp-:sm:text-left", className)}
      {...props}
    />
  )
}

function DialogFooter({
  className,
  showCloseButton = false,
  children,
  ...props
}: React.ComponentProps<"div"> & {
  showCloseButton?: boolean
}) {
  return (
    <div
      data-slot="dialog-footer"
      className={cn(
        "fp-:flex fp-:flex-col-reverse fp-:gap-2 fp-:sm:flex-row fp-:sm:justify-end",
        className
      )}
      {...props}
    >
      {children}
      {showCloseButton && (
        <DialogPrimitive.Close asChild>
          <Button variant="outline">Close</Button>
        </DialogPrimitive.Close>
      )}
    </div>
  )
}

function DialogTitle({
  className,
  ...props
}: React.ComponentProps<typeof DialogPrimitive.Title>) {
  return (
    <DialogPrimitive.Title
      data-slot="dialog-title"
      className={cn("fp-:text-lg fp-:leading-none fp-:font-semibold", className)}
      {...props}
    />
  )
}

function DialogDescription({
  className,
  ...props
}: React.ComponentProps<typeof DialogPrimitive.Description>) {
  return (
    <DialogPrimitive.Description
      data-slot="dialog-description"
      className={cn("fp-:text-sm fp-:text-muted-foreground", className)}
      {...props}
    />
  )
}

export {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogOverlay,
  DialogPortal,
  DialogTitle,
  DialogTrigger,
}
