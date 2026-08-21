"use client";

import Link from "next/link";
import { useState } from "react";

import { Button } from "@/components/ui/button";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { Checkbox } from "@/components/ui/checkbox";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { useUser } from "@/hooks/use-user";
import {
  useCreateStrategyGoal,
  useDeleteStrategyGoal,
  useStrategyGoals,
  useUpdateStrategyGoal,
} from "@/hooks/use-strategy-goals";
import { PERSPECTIVES, PERSPECTIVE_LABELS } from "@/types/strategy-goal";
import type { StrategyGoal, StrategyGoalPayload } from "@/types/strategy-goal";
import { StrategyGoalForm } from "./strategy-goal-form";

const ALL_PERSPECTIVES = "all";

export default function StrategyGoalsPage() {
  const { data: user, isPending: isUserPending } = useUser();

  const [perspectiveFilter, setPerspectiveFilter] = useState<string>(ALL_PERSPECTIVES);
  const [formOpen, setFormOpen] = useState(false);
  const [editingGoal, setEditingGoal] = useState<StrategyGoal | undefined>(undefined);
  const [deletingGoal, setDeletingGoal] = useState<StrategyGoal | undefined>(undefined);

  const { data: goals, isLoading } = useStrategyGoals(
    perspectiveFilter === ALL_PERSPECTIVES ? undefined : perspectiveFilter
  );
  const createGoal = useCreateStrategyGoal();
  const updateGoal = useUpdateStrategyGoal();
  const deleteGoal = useDeleteStrategyGoal();

  if (isUserPending) {
    return null;
  }

  if (!user) {
    return (
      <div className="flex flex-1 items-center justify-center p-4">
        <p>
          ログインが必要です。
          <Link href="/login" className="text-primary underline">
            ログインページへ
          </Link>
        </p>
      </div>
    );
  }

  function openCreateForm() {
    setEditingGoal(undefined);
    setFormOpen(true);
  }

  function openEditForm(goal: StrategyGoal) {
    setEditingGoal(goal);
    setFormOpen(true);
  }

  function handleSubmit(payload: StrategyGoalPayload) {
    if (editingGoal) {
      updateGoal.mutate(
        { id: editingGoal.id, payload },
        { onSuccess: () => setFormOpen(false) }
      );
    } else {
      createGoal.mutate(payload, { onSuccess: () => setFormOpen(false) });
    }
  }

  function toggleAdopted(goal: StrategyGoal, adopted: boolean) {
    updateGoal.mutate({
      id: goal.id,
      payload: {
        fiscal_year_id: goal.fiscal_year_id,
        department_id: goal.department_id,
        perspective: goal.perspective,
        title: goal.title,
        definition: goal.definition,
        importance: goal.importance,
        owner_user_id: goal.owner_user_id,
        is_adopted: adopted,
      },
    });
  }

  function handleDeleteConfirm() {
    if (!deletingGoal) return;
    deleteGoal.mutate(deletingGoal.id, { onSuccess: () => setDeletingGoal(undefined) });
  }

  return (
    <div className="flex flex-1 flex-col gap-4 p-4">
      <div className="flex items-center justify-between">
        <h1 className="text-lg font-bold">戦略目標</h1>
        <Button onClick={openCreateForm}>新規登録</Button>
      </div>

      <div className="flex items-center gap-2">
        <Select value={perspectiveFilter} onValueChange={(value) => setPerspectiveFilter(String(value))}>
          <SelectTrigger className="w-48">
            <SelectValue placeholder="視点で絞り込み" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value={ALL_PERSPECTIVES}>すべての視点</SelectItem>
            {PERSPECTIVES.map((p) => (
              <SelectItem key={p} value={p}>
                {PERSPECTIVE_LABELS[p]}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>

      <div className="overflow-x-auto rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>採用</TableHead>
              <TableHead>視点</TableHead>
              <TableHead>戦略目標</TableHead>
              <TableHead>部門属性</TableHead>
              <TableHead>定義</TableHead>
              <TableHead>重要度</TableHead>
              <TableHead>責任者</TableHead>
              <TableHead>操作</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {isLoading && (
              <TableRow>
                <TableCell colSpan={8}>読み込み中...</TableCell>
              </TableRow>
            )}
            {!isLoading && goals?.length === 0 && (
              <TableRow>
                <TableCell colSpan={8}>戦略目標がまだ登録されていません。</TableCell>
              </TableRow>
            )}
            {goals?.map((goal) => (
              <TableRow key={goal.id}>
                <TableCell>
                  <Checkbox
                    checked={goal.is_adopted}
                    onCheckedChange={(checked) => toggleAdopted(goal, checked === true)}
                  />
                </TableCell>
                <TableCell>{PERSPECTIVE_LABELS[goal.perspective]}</TableCell>
                <TableCell>{goal.title}</TableCell>
                <TableCell>{goal.department_name ?? "全社"}</TableCell>
                <TableCell className="max-w-xs truncate">{goal.definition}</TableCell>
                <TableCell>{goal.importance}</TableCell>
                <TableCell>{goal.owner_name}</TableCell>
                <TableCell className="flex gap-2">
                  <Button variant="outline" size="sm" onClick={() => openEditForm(goal)}>
                    編集
                  </Button>
                  <Button variant="destructive" size="sm" onClick={() => setDeletingGoal(goal)}>
                    削除
                  </Button>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>

      <StrategyGoalForm
        open={formOpen}
        onOpenChange={setFormOpen}
        goal={editingGoal}
        onSubmit={handleSubmit}
        isSubmitting={createGoal.isPending || updateGoal.isPending}
        error={createGoal.error ?? updateGoal.error}
      />

      <AlertDialog open={!!deletingGoal} onOpenChange={(open) => !open && setDeletingGoal(undefined)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>戦略目標を削除しますか？</AlertDialogTitle>
            <AlertDialogDescription>
              「{deletingGoal?.title}」を削除します。この操作は元に戻せません。
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>キャンセル</AlertDialogCancel>
            <AlertDialogAction onClick={handleDeleteConfirm}>削除する</AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
