"use client";

import { useState } from "react";

import { FieldLabel } from "@/components/field-label";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import { useCompanyUsers, useDepartments, useFiscalYears } from "@/hooks/use-lookups";
import { ApiError } from "@/lib/api/client";
import { PERSPECTIVES, PERSPECTIVE_LABELS } from "@/types/strategy-goal";
import type { Perspective, StrategyGoal, StrategyGoalPayload } from "@/types/strategy-goal";

const NO_DEPARTMENT = "none";

type StrategyGoalFormProps = {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  goal?: StrategyGoal;
  onSubmit: (payload: StrategyGoalPayload) => void;
  isSubmitting: boolean;
  error?: unknown;
};

export function StrategyGoalForm({
  open,
  onOpenChange,
  goal,
  onSubmit,
  isSubmitting,
  error,
}: StrategyGoalFormProps) {
  const { data: fiscalYears } = useFiscalYears();
  const { data: departments } = useDepartments();
  const { data: users } = useCompanyUsers();

  const [fiscalYearId, setFiscalYearId] = useState<string>(
    goal ? String(goal.fiscal_year_id) : ""
  );
  const [departmentId, setDepartmentId] = useState<string>(
    goal?.department_id ? String(goal.department_id) : NO_DEPARTMENT
  );
  const [perspective, setPerspective] = useState<Perspective | "">(goal?.perspective ?? "");
  const [title, setTitle] = useState(goal?.title ?? "");
  const [definition, setDefinition] = useState(goal?.definition ?? "");
  const [importance, setImportance] = useState(goal ? String(goal.importance) : "3");
  const [ownerUserId, setOwnerUserId] = useState<string>(
    goal ? String(goal.owner_user_id) : ""
  );
  const [isAdopted, setIsAdopted] = useState(goal?.is_adopted ?? true);

  const errorMessage = error instanceof ApiError ? error.message : undefined;

  function handleSubmit(event: React.FormEvent) {
    event.preventDefault();

    if (!fiscalYearId || !perspective || !ownerUserId) {
      return;
    }

    onSubmit({
      fiscal_year_id: Number(fiscalYearId),
      department_id: departmentId === NO_DEPARTMENT ? null : Number(departmentId),
      perspective,
      title,
      definition: definition || null,
      importance: Number(importance),
      owner_user_id: Number(ownerUserId),
      is_adopted: isAdopted,
    });
  }

  return (
    <Dialog open={open} onOpenChange={(next) => onOpenChange(next)}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{goal ? "戦略目標を編集" : "戦略目標を新規登録"}</DialogTitle>
        </DialogHeader>
        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
          <div className="flex flex-col gap-2">
            <Label htmlFor="fiscal_year">年度</Label>
            <Select value={fiscalYearId} onValueChange={(value) => setFiscalYearId(String(value))}>
              <SelectTrigger id="fiscal_year">
                <SelectValue placeholder="年度を選択" />
              </SelectTrigger>
              <SelectContent>
                {fiscalYears?.map((fy) => (
                  <SelectItem key={fy.id} value={String(fy.id)}>
                    {fy.year}年度
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <div className="flex flex-col gap-2">
            <Label htmlFor="perspective">視点</Label>
            <Select
              value={perspective}
              onValueChange={(value) => setPerspective(value as Perspective)}
            >
              <SelectTrigger id="perspective">
                <SelectValue placeholder="視点を選択" />
              </SelectTrigger>
              <SelectContent>
                {PERSPECTIVES.map((p) => (
                  <SelectItem key={p} value={p}>
                    {PERSPECTIVE_LABELS[p]}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <div className="flex flex-col gap-2">
            <Label htmlFor="title">戦略目標</Label>
            <Input
              id="title"
              required
              value={title}
              onChange={(event) => setTitle(event.target.value)}
            />
          </div>

          <div className="flex flex-col gap-2">
            <Label htmlFor="department">部門属性</Label>
            <Select value={departmentId} onValueChange={(value) => setDepartmentId(String(value))}>
              <SelectTrigger id="department">
                <SelectValue placeholder="部門を選択（任意）" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value={NO_DEPARTMENT}>全社</SelectItem>
                {departments?.map((department) => (
                  <SelectItem key={department.id} value={String(department.id)}>
                    {department.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <div className="flex flex-col gap-2">
            <FieldLabel htmlFor="definition" help="この戦略目標が何を目指すのかを具体的に説明します">
              定義
            </FieldLabel>
            <Textarea
              id="definition"
              value={definition}
              onChange={(event) => setDefinition(event.target.value)}
            />
          </div>

          <div className="flex flex-col gap-2">
            <FieldLabel htmlFor="importance" help="この戦略目標がどれだけ重要かを1（低い）〜5（高い）で表します">
              重要度（1〜5）
            </FieldLabel>
            <Input
              id="importance"
              type="number"
              min={1}
              max={5}
              required
              value={importance}
              onChange={(event) => setImportance(event.target.value)}
            />
          </div>

          <div className="flex flex-col gap-2">
            <Label htmlFor="owner">責任者</Label>
            <Select value={ownerUserId} onValueChange={(value) => setOwnerUserId(String(value))}>
              <SelectTrigger id="owner">
                <SelectValue placeholder="責任者を選択" />
              </SelectTrigger>
              <SelectContent>
                {users?.map((user) => (
                  <SelectItem key={user.id} value={String(user.id)}>
                    {user.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <div className="flex items-center gap-2">
            <Checkbox
              id="is_adopted"
              checked={isAdopted}
              onCheckedChange={(checked) => setIsAdopted(checked === true)}
            />
            <FieldLabel
              htmlFor="is_adopted"
              help="チェックを外すと候補として保持したまま一覧の対象から実質的な運用対象外にできます（削除はしません）"
            >
              採用する
            </FieldLabel>
          </div>

          {errorMessage && <p className="text-sm text-destructive">{errorMessage}</p>}

          <DialogFooter>
            <Button type="submit" disabled={isSubmitting}>
              {isSubmitting ? "保存中..." : "保存"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
