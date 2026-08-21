export const PERSPECTIVES = ["financial", "customer", "process", "learning"] as const;

export type Perspective = (typeof PERSPECTIVES)[number];

export const PERSPECTIVE_LABELS: Record<Perspective, string> = {
  financial: "財務",
  customer: "顧客",
  process: "業務プロセス",
  learning: "学習と成長",
};

export type StrategyGoal = {
  id: number;
  fiscal_year_id: number;
  department_id: number | null;
  department_name: string | null;
  perspective: Perspective;
  title: string;
  definition: string | null;
  importance: number;
  owner_user_id: number;
  owner_name: string | null;
  is_adopted: boolean;
  created_at: string;
  updated_at: string;
};

export type StrategyGoalPayload = {
  fiscal_year_id: number;
  department_id: number | null;
  perspective: Perspective;
  title: string;
  definition: string | null;
  importance: number;
  owner_user_id: number;
  is_adopted?: boolean;
};
