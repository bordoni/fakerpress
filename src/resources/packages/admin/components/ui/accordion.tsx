"use client"

import * as React from "react"
import { ChevronDownIcon } from "lucide-react"
import { Accordion as AccordionPrimitive } from "radix-ui"

import { cn } from "@fp/admin/lib/utils"

function Accordion({
  ...props
}: React.ComponentProps<typeof AccordionPrimitive.Root>) {
  return <AccordionPrimitive.Root data-slot="accordion" {...props} />
}

function AccordionItem({
  className,
  ...props
}: React.ComponentProps<typeof AccordionPrimitive.Item>) {
  return (
    <AccordionPrimitive.Item
      data-slot="accordion-item"
      className={cn("fp-:border-b fp-:last:border-b-0", className)}
      {...props}
    />
  )
}

function AccordionTrigger({
  className,
  children,
  ...props
}: React.ComponentProps<typeof AccordionPrimitive.Trigger>) {
  return (
    <AccordionPrimitive.Header className="fp-:flex">
      <AccordionPrimitive.Trigger
        data-slot="accordion-trigger"
        className={cn(
          "fp-:flex fp-:flex-1 fp-:items-start fp-:justify-between fp-:gap-4 fp-:rounded-md fp-:py-4 fp-:text-left fp-:text-sm fp-:font-medium fp-:transition-all fp-:outline-none fp-:hover:underline fp-:focus-visible:border-ring fp-:focus-visible:ring-[3px] fp-:focus-visible:ring-ring/50 fp-:disabled:pointer-events-none fp-:disabled:opacity-50 fp-:[&[data-state=open]>svg]:rotate-180",
          className
        )}
        {...props}
      >
        {children}
        <ChevronDownIcon className="fp-:pointer-events-none fp-:size-4 fp-:shrink-0 fp-:translate-y-0.5 fp-:text-muted-foreground fp-:transition-transform fp-:duration-200" />
      </AccordionPrimitive.Trigger>
    </AccordionPrimitive.Header>
  )
}

function AccordionContent({
  className,
  children,
  ...props
}: React.ComponentProps<typeof AccordionPrimitive.Content>) {
  return (
    <AccordionPrimitive.Content
      data-slot="accordion-content"
      className="fp-:overflow-hidden fp-:text-sm fp-:data-[state=closed]:animate-accordion-up fp-:data-[state=open]:animate-accordion-down"
      {...props}
    >
      <div className={cn("fp-:pt-0 fp-:pb-4", className)}>{children}</div>
    </AccordionPrimitive.Content>
  )
}

export { Accordion, AccordionItem, AccordionTrigger, AccordionContent }
