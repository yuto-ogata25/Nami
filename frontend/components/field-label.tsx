"use client";

import { HelpCircleIcon } from "lucide-react";

import { Label } from "@/components/ui/label";
import { Tooltip, TooltipContent, TooltipTrigger } from "@/components/ui/tooltip";

type FieldLabelProps = {
  htmlFor: string;
  children: React.ReactNode;
  help?: string;
};

// フォーム項目名の意味が分かりづらいメンバー向けに、任意で「？」アイコンのホバー説明を添えられるLabel。
export function FieldLabel({ htmlFor, children, help }: FieldLabelProps) {
  return (
    <div className="flex items-center gap-1">
      <Label htmlFor={htmlFor}>{children}</Label>
      {help && (
        <Tooltip>
          <TooltipTrigger
            type="button"
            className="text-muted-foreground hover:text-foreground"
            aria-label={`${htmlFor}の説明`}
          >
            <HelpCircleIcon className="size-3.5" />
          </TooltipTrigger>
          <TooltipContent>{help}</TooltipContent>
        </Tooltip>
      )}
    </div>
  );
}
