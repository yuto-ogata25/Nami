"use client";

import { useState } from "react";

import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { FieldLabel } from "@/components/field-label";
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
import { useCompanyUsers } from "@/hooks/use-lookups";
import { useStrategyGoals } from "@/hooks/use-strategy-goals";
import { ApiError } from "@/lib/api/client";
import {
  AGGREGATION_TYPES,
  AGGREGATION_TYPE_LABELS,
  POLARITIES,
  POLARITY_LABELS,
} from "@/types/kpi";
import type { AggregationType, Kpi, KpiPayload, Polarity } from "@/types/kpi";

type KpiFormProps = {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  kpi?: Kpi;
  onSubmit: (payload: KpiPayload) => void;
  isSubmitting: boolean;
  error?: unknown;
};

export function KpiForm({ open, onOpenChange, kpi, onSubmit, isSubmitting, error }: KpiFormProps) {
  // 戦略目標との紐づけプルダウンには採用状態を問わず全件を対象とする
  const { data: strategyGoals } = useStrategyGoals();
  const { data: users } = useCompanyUsers();

  const [strategyGoalId, setStrategyGoalId] = useState<string>(
    kpi ? String(kpi.strategy_goal_id) : ""
  );
  const [name, setName] = useState(kpi?.name ?? "");
  const [definition, setDefinition] = useState(kpi?.definition ?? "");
  const [ownerUserId, setOwnerUserId] = useState<string>(kpi ? String(kpi.owner_user_id) : "");
  const [importance, setImportance] = useState(kpi ? String(kpi.importance) : "3");
  const [unit, setUnit] = useState(kpi?.unit ?? "");
  const [polarity, setPolarity] = useState<Polarity | "">(kpi?.polarity ?? "");
  const [aggregationType, setAggregationType] = useState<AggregationType | "">(
    kpi?.aggregation_type ?? ""
  );
  const [note, setNote] = useState(kpi?.note ?? "");

  const errorMessage = error instanceof ApiError ? error.message : undefined;

  function handleSubmit(event: React.FormEvent) {
    event.preventDefault();

    if (!strategyGoalId || !ownerUserId || !polarity || !aggregationType) {
      return;
    }

    onSubmit({
      strategy_goal_id: Number(strategyGoalId),
      name,
      definition: definition || null,
      owner_user_id: Number(ownerUserId),
      importance: Number(importance),
      unit,
      polarity,
      aggregation_type: aggregationType,
      note: note || null,
    });
  }

  return (
    <Dialog open={open} onOpenChange={(next) => onOpenChange(next)}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{kpi ? "KPIを編集" : "KPIを新規登録"}</DialogTitle>
        </DialogHeader>
        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
          <div className="flex flex-col gap-2">
            <Label htmlFor="strategy_goal">戦略目標</Label>
            <Select
              value={strategyGoalId}
              onValueChange={(value) => setStrategyGoalId(String(value))}
            >
              <SelectTrigger id="strategy_goal">
                <SelectValue placeholder="戦略目標を選択" />
              </SelectTrigger>
              <SelectContent>
                {strategyGoals?.map((goal) => (
                  <SelectItem key={goal.id} value={String(goal.id)}>
                    {goal.title}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <div className="flex flex-col gap-2">
            <FieldLabel htmlFor="name" help="測定する対象の名前です。例：売上高、顧客満足度">
              指標
            </FieldLabel>
            <Input
              id="name"
              required
              value={name}
              onChange={(event) => setName(event.target.value)}
            />
          </div>

          <div className="flex flex-col gap-2">
            <FieldLabel htmlFor="definition" help="この指標が何を意味し、どう計算するかを説明します">
              定義
            </FieldLabel>
            <Textarea
              id="definition"
              value={definition}
              onChange={(event) => setDefinition(event.target.value)}
            />
          </div>

          <div className="flex flex-col gap-2">
            <Label htmlFor="owner">担当者</Label>
            <Select value={ownerUserId} onValueChange={(value) => setOwnerUserId(String(value))}>
              <SelectTrigger id="owner">
                <SelectValue placeholder="担当者を選択" />
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

          <div className="flex flex-col gap-2">
            <FieldLabel htmlFor="importance" help="この指標がどれだけ重要かを1（低い）〜5（高い）で表します">
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
            <FieldLabel htmlFor="unit" help="測定値の単位です。例：円、%、件、人">
              単位
            </FieldLabel>
            <Input
              id="unit"
              required
              placeholder="円 / % / 件 など"
              value={unit}
              onChange={(event) => setUnit(event.target.value)}
            />
          </div>

          <div className="flex flex-col gap-2">
            <FieldLabel
              htmlFor="polarity"
              help="positiveは数値が上がるほど良い指標（例：売上）、negativeは数値が下がるほど良い指標（例：コスト、離職率）です"
            >
              極性
            </FieldLabel>
            <Select value={polarity} onValueChange={(value) => setPolarity(value as Polarity)}>
              <SelectTrigger id="polarity">
                <SelectValue placeholder="極性を選択" />
              </SelectTrigger>
              <SelectContent>
                {POLARITIES.map((p) => (
                  <SelectItem key={p} value={p}>
                    {POLARITY_LABELS[p]}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <div className="flex flex-col gap-2">
            <FieldLabel
              htmlFor="aggregation_type"
              help="sumは月々の値を積み上げる指標（例：売上）、averageは平均を使う指標、latestは最新の値をそのまま使う指標（例：満足度アンケート）です"
            >
              集計方法
            </FieldLabel>
            <Select
              value={aggregationType}
              onValueChange={(value) => setAggregationType(value as AggregationType)}
            >
              <SelectTrigger id="aggregation_type">
                <SelectValue placeholder="集計方法を選択" />
              </SelectTrigger>
              <SelectContent>
                {AGGREGATION_TYPES.map((a) => (
                  <SelectItem key={a} value={a}>
                    {AGGREGATION_TYPE_LABELS[a]}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <div className="flex flex-col gap-2">
            <Label htmlFor="note">備考</Label>
            <Textarea id="note" value={note} onChange={(event) => setNote(event.target.value)} />
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
