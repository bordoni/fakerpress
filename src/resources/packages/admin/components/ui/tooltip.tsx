"use client"

import * as React from "react"
import { Tooltip as TooltipPrimitive } from "radix-ui"

import { cn } from "@fp/admin/lib/utils"

function TooltipProvider({
  delayDuration = 0,
  ...props
}: React.ComponentProps<typeof TooltipPrimitive.Provider>) {
  return (
    <TooltipPrimitive.Provider
      data-slot="tooltip-provider"
      delayDuration={delayDuration}
      {...props}
    />
  )
}

function Tooltip({
  ...props
}: React.ComponentProps<typeof TooltipPrimitive.Root>) {
  return <TooltipPrimitive.Root data-slot="tooltip" {...props} />
}

function TooltipTrigger({
  ...props
}: React.ComponentProps<typeof TooltipPrimitive.Trigger>) {
  return <TooltipPrimitive.Trigger data-slot="tooltip-trigger" {...props} />
}

function TooltipContent({
  className,
  sideOffset = 0,
  children,
  ...props
}: React.ComponentProps<typeof TooltipPrimitive.Content>) {
  return (
    <TooltipPrimitive.Portal>
      <TooltipPrimitive.Content
        data-slot="tooltip-content"
        sideOffset={sideOffset}
        className={cn(
          "fp:z-50 fp:w-fit fp:origin-(--radix-tooltip-content-transform-origin) fp:animate-in fp:rounded-md fp:bg-foreground fp:px-3 fp:py-1.5 fp:text-xs fp:text-balance fp:text-background fp:fade-in-0 fp:zoom-in-95 fp:data-[side=bottom]:slide-in-from-top-2 fp:data-[side=left]:slide-in-from-right-2 fp:data-[side=right]:slide-in-from-left-2 fp:data-[side=top]:slide-in-from-bottom-2 fp:data-[state=closed]:animate-out fp:data-[state=closed]:fade-out-0 fp:data-[state=closed]:zoom-out-95",
          className
        )}
        {...props}
      >
        {children}
        <TooltipPrimitive.Arrow className="fp:z-50 fp:size-2.5 fp:translate-y-[calc(-50%_-_2px)] fp:rotate-45 fp:rounded-[2px] fp:bg-foreground fp:fill-foreground" />
      </TooltipPrimitive.Content>
    </TooltipPrimitive.Portal>
  )
}

export { Tooltip, TooltipTrigger, TooltipContent, TooltipProvider }
