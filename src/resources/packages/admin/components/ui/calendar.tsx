import * as React from "react"
import {
  ChevronDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
} from "lucide-react"
import {
  DayPicker,
  getDefaultClassNames,
  type DayButton,
} from "react-day-picker"

import { cn } from "@fp/admin/lib/utils"
import { Button, buttonVariants } from "@fp/admin/components/ui/button"

function Calendar({
  className,
  classNames,
  showOutsideDays = true,
  captionLayout = "label",
  buttonVariant = "ghost",
  formatters,
  components,
  ...props
}: React.ComponentProps<typeof DayPicker> & {
  buttonVariant?: React.ComponentProps<typeof Button>["variant"]
}) {
  const defaultClassNames = getDefaultClassNames()

  return (
    <DayPicker
      showOutsideDays={showOutsideDays}
      className={cn(
        "fp-:group/calendar fp-:bg-background fp-:p-3 fp-:[--cell-size:--spacing(8)] fp-:[[data-slot=card-content]_&]:bg-transparent fp-:[[data-slot=popover-content]_&]:bg-transparent",
        String.raw`rtl:**:[.rdp-button\_next>svg]:rotate-180`,
        String.raw`rtl:**:[.rdp-button\_previous>svg]:rotate-180`,
        className
      )}
      captionLayout={captionLayout}
      formatters={{
        formatMonthDropdown: (date) =>
          date.toLocaleString("default", { month: "short" }),
        ...formatters,
      }}
      classNames={{
        root: cn("fp-:w-fit", defaultClassNames.root),
        months: cn(
          "fp-:relative fp-:flex fp-:flex-col fp-:gap-4 fp-:md:flex-row",
          defaultClassNames.months
        ),
        month: cn("fp-:flex fp-:w-full fp-:flex-col fp-:gap-4", defaultClassNames.month),
        nav: cn(
          "fp-:absolute fp-:inset-x-0 fp-:top-0 fp-:flex fp-:w-full fp-:items-center fp-:justify-between fp-:gap-1",
          defaultClassNames.nav
        ),
        button_previous: cn(
          buttonVariants({ variant: buttonVariant }),
          "fp-:size-(--cell-size) fp-:p-0 fp-:select-none fp-:aria-disabled:opacity-50",
          defaultClassNames.button_previous
        ),
        button_next: cn(
          buttonVariants({ variant: buttonVariant }),
          "fp-:size-(--cell-size) fp-:p-0 fp-:select-none fp-:aria-disabled:opacity-50",
          defaultClassNames.button_next
        ),
        month_caption: cn(
          "fp-:flex fp-:h-(--cell-size) fp-:w-full fp-:items-center fp-:justify-center fp-:px-(--cell-size)",
          defaultClassNames.month_caption
        ),
        dropdowns: cn(
          "fp-:flex fp-:h-(--cell-size) fp-:w-full fp-:items-center fp-:justify-center fp-:gap-1.5 fp-:text-sm fp-:font-medium",
          defaultClassNames.dropdowns
        ),
        dropdown_root: cn(
          "fp-:relative fp-:rounded-md fp-:border fp-:border-input fp-:shadow-xs fp-:has-focus:border-ring fp-:has-focus:ring-[3px] fp-:has-focus:ring-ring/50",
          defaultClassNames.dropdown_root
        ),
        dropdown: cn(
          "fp-:absolute fp-:inset-0 fp-:bg-popover fp-:opacity-0",
          defaultClassNames.dropdown
        ),
        caption_label: cn(
          "fp-:font-medium fp-:select-none",
          captionLayout === "label"
            ? "fp-:text-sm"
            : "fp-:flex fp-:h-8 fp-:items-center fp-:gap-1 fp-:rounded-md fp-:pr-1 fp-:pl-2 fp-:text-sm fp-:[&>svg]:size-3.5 fp-:[&>svg]:text-muted-foreground",
          defaultClassNames.caption_label
        ),
        table: "fp-:w-full fp-:border-collapse",
        weekdays: cn("fp-:flex", defaultClassNames.weekdays),
        weekday: cn(
          "fp-:flex-1 fp-:rounded-md fp-:text-[0.8rem] fp-:font-normal fp-:text-muted-foreground fp-:select-none",
          defaultClassNames.weekday
        ),
        week: cn("fp-:mt-2 fp-:flex fp-:w-full", defaultClassNames.week),
        week_number_header: cn(
          "fp-:w-(--cell-size) fp-:select-none",
          defaultClassNames.week_number_header
        ),
        week_number: cn(
          "fp-:text-[0.8rem] fp-:text-muted-foreground fp-:select-none",
          defaultClassNames.week_number
        ),
        day: cn(
          "fp-:group/day fp-:relative fp-:aspect-square fp-:h-full fp-:w-full fp-:p-0 fp-:text-center fp-:select-none fp-:[&:last-child[data-selected=true]_button]:rounded-r-md",
          props.showWeekNumber
            ? "fp-:[&:nth-child(2)[data-selected=true]_button]:rounded-l-md"
            : "fp-:[&:first-child[data-selected=true]_button]:rounded-l-md",
          defaultClassNames.day
        ),
        range_start: cn(
          "fp-:rounded-l-md fp-:bg-accent",
          defaultClassNames.range_start
        ),
        range_middle: cn("fp-:rounded-none", defaultClassNames.range_middle),
        range_end: cn("fp-:rounded-r-md fp-:bg-accent", defaultClassNames.range_end),
        today: cn(
          "fp-:rounded-md fp-:bg-accent fp-:text-accent-foreground fp-:data-[selected=true]:rounded-none",
          defaultClassNames.today
        ),
        outside: cn(
          "fp-:text-muted-foreground fp-:aria-selected:text-muted-foreground",
          defaultClassNames.outside
        ),
        disabled: cn(
          "fp-:text-muted-foreground fp-:opacity-50",
          defaultClassNames.disabled
        ),
        hidden: cn("fp-:invisible", defaultClassNames.hidden),
        ...classNames,
      }}
      components={{
        Root: ({ className, rootRef, ...props }) => {
          return (
            <div
              data-slot="calendar"
              ref={rootRef}
              className={cn(className)}
              {...props}
            />
          )
        },
        Chevron: ({ className, orientation, ...props }) => {
          if (orientation === "left") {
            return (
              <ChevronLeftIcon className={cn("fp-:size-4", className)} {...props} />
            )
          }

          if (orientation === "right") {
            return (
              <ChevronRightIcon
                className={cn("fp-:size-4", className)}
                {...props}
              />
            )
          }

          return (
            <ChevronDownIcon className={cn("fp-:size-4", className)} {...props} />
          )
        },
        DayButton: CalendarDayButton,
        WeekNumber: ({ children, ...props }) => {
          return (
            <td {...props}>
              <div className="fp-:flex fp-:size-(--cell-size) fp-:items-center fp-:justify-center fp-:text-center">
                {children}
              </div>
            </td>
          )
        },
        ...components,
      }}
      {...props}
    />
  )
}

function CalendarDayButton({
  className,
  day,
  modifiers,
  ...props
}: React.ComponentProps<typeof DayButton>) {
  const defaultClassNames = getDefaultClassNames()

  const ref = React.useRef<HTMLButtonElement>(null)
  React.useEffect(() => {
    if (modifiers.focused) ref.current?.focus()
  }, [modifiers.focused])

  return (
    <Button
      ref={ref}
      variant="ghost"
      size="icon"
      data-day={day.date.toLocaleDateString()}
      data-selected-single={
        modifiers.selected &&
        !modifiers.range_start &&
        !modifiers.range_end &&
        !modifiers.range_middle
      }
      data-range-start={modifiers.range_start}
      data-range-end={modifiers.range_end}
      data-range-middle={modifiers.range_middle}
      className={cn(
        "fp-:flex fp-:aspect-square fp-:size-auto fp-:w-full fp-:min-w-(--cell-size) fp-:flex-col fp-:gap-1 fp-:leading-none fp-:font-normal fp-:group-data-[focused=true]/day:relative fp-:group-data-[focused=true]/day:z-10 fp-:group-data-[focused=true]/day:border-ring fp-:group-data-[focused=true]/day:ring-[3px] fp-:group-data-[focused=true]/day:ring-ring/50 fp-:data-[range-end=true]:rounded-md fp-:data-[range-end=true]:rounded-r-md fp-:data-[range-end=true]:bg-primary fp-:data-[range-end=true]:text-primary-foreground fp-:data-[range-middle=true]:rounded-none fp-:data-[range-middle=true]:bg-accent fp-:data-[range-middle=true]:text-accent-foreground fp-:data-[range-start=true]:rounded-md fp-:data-[range-start=true]:rounded-l-md fp-:data-[range-start=true]:bg-primary fp-:data-[range-start=true]:text-primary-foreground fp-:data-[selected-single=true]:bg-primary fp-:data-[selected-single=true]:text-primary-foreground fp-:dark:hover:text-accent-foreground fp-:[&>span]:text-xs fp-:[&>span]:opacity-70",
        defaultClassNames.day,
        className
      )}
      {...props}
    />
  )
}

export { Calendar, CalendarDayButton }
