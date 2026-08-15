export type User = {
  id: number;
  name: string;
  email: string;
  company_id: number;
  department_id: number | null;
  role: string;
};

export type Operator = {
  id: number;
  name: string;
  email: string;
  role: "owner" | "staff";
};

export type LoginPayload = {
  email: string;
  password: string;
};
