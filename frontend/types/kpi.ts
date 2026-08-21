import type { Perspective } from "@/types/strategy-goal";

export const POLARITIES = ["positive", "negative"] as const;
export type Polarity = (typeof POLARITIES)[number];
export const POLARITY_LABELS: Record<Polarity, string> = {
  positive: "positive（上昇が良い）",
  negative: "negative（下降が良い）",
};

export const AGGREGATION_TYPES = ["sum", "average", "latest"] as const;
export type AggregationType = (typeof AGGREGATION_TYPES)[number];
export const AGGREGATION_TYPE_LABELS: Record<AggregationType, string> = {
  sum: "sum（累計）",
  average: "average（平均）",
  latest: "latest（最新値）",
};

export type Kpi = {
  id: number;
  strategy_goal_id: number;
  strategy_goal_title: string | null;
  perspective: Perspective | null;
  department_name: string | null;
  name: string;
  definition: string | null;
  owner_user_id: number;
  owner_name: string | null;
  importance: number;
  unit: string;
  polarity: Polarity;
  aggregation_type: AggregationType;
  note: string | null;
  created_at: string;
  updated_at: string;
};

export type KpiPayload = {
  strategy_goal_id: number;
  name: string;
  definition: string | null;
  owner_user_id: number;
  importance: number;
  unit: string;
  polarity: Polarity;
  aggregation_type: AggregationType;
  note: string | null;
};
