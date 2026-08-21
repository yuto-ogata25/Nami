"use client";

import Link from "next/link";
import { useState } from "react";

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
import { Button } from "@/components/ui/button";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { useCreateKpi, useDeleteKpi, useKpis, useUpdateKpi } from "@/hooks/use-kpis";
import { useUser } from "@/hooks/use-user";
import { PERSPECTIVE_LABELS } from "@/types/strategy-goal";
import type { Kpi, KpiPayload } from "@/types/kpi";
import { KpiForm } from "./kpi-form";

export default function KpisPage() {
  const { data: user, isPending: isUserPending } = useUser();

  const [formOpen, setFormOpen] = useState(false);
  const [editingKpi, setEditingKpi] = useState<Kpi | undefined>(undefined);
  const [deletingKpi, setDeletingKpi] = useState<Kpi | undefined>(undefined);

  const { data: kpis, isLoading } = useKpis();
  const createKpi = useCreateKpi();
  const updateKpi = useUpdateKpi();
  const deleteKpi = useDeleteKpi();

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
    setEditingKpi(undefined);
    setFormOpen(true);
  }

  function openEditForm(kpi: Kpi) {
    setEditingKpi(kpi);
    setFormOpen(true);
  }

  function handleSubmit(payload: KpiPayload) {
    if (editingKpi) {
      updateKpi.mutate({ id: editingKpi.id, payload }, { onSuccess: () => setFormOpen(false) });
    } else {
      createKpi.mutate(payload, { onSuccess: () => setFormOpen(false) });
    }
  }

  function handleDeleteConfirm() {
    if (!deletingKpi) return;
    deleteKpi.mutate(deletingKpi.id, { onSuccess: () => setDeletingKpi(undefined) });
  }

  return (
    <div className="flex flex-1 flex-col gap-4 p-4">
      <div className="flex items-center justify-between">
        <h1 className="text-lg font-bold">KPI</h1>
        <div className="flex gap-2">
          <Link href="/strategy-goals" className="text-sm text-primary underline self-center">
            戦略目標一覧へ
          </Link>
          <Button onClick={openCreateForm}>新規登録</Button>
        </div>
      </div>

      <div className="overflow-x-auto rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>視点</TableHead>
              <TableHead>部門属性</TableHead>
              <TableHead>戦略目標</TableHead>
              <TableHead>指標</TableHead>
              <TableHead>定義</TableHead>
              <TableHead>担当者</TableHead>
              <TableHead>重要度</TableHead>
              <TableHead>単位</TableHead>
              <TableHead>極性</TableHead>
              <TableHead>備考</TableHead>
              <TableHead>操作</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {isLoading && (
              <TableRow>
                <TableCell colSpan={11}>読み込み中...</TableCell>
              </TableRow>
            )}
            {!isLoading && kpis?.length === 0 && (
              <TableRow>
                <TableCell colSpan={11}>KPIがまだ登録されていません。</TableCell>
              </TableRow>
            )}
            {kpis?.map((kpi) => (
              <TableRow key={kpi.id}>
                <TableCell>{kpi.perspective ? PERSPECTIVE_LABELS[kpi.perspective] : "-"}</TableCell>
                <TableCell>{kpi.department_name ?? "全社"}</TableCell>
                <TableCell>{kpi.strategy_goal_title}</TableCell>
                <TableCell>{kpi.name}</TableCell>
                <TableCell className="max-w-xs truncate">{kpi.definition}</TableCell>
                <TableCell>{kpi.owner_name}</TableCell>
                <TableCell>{kpi.importance}</TableCell>
                <TableCell>{kpi.unit}</TableCell>
                <TableCell>{kpi.polarity}</TableCell>
                <TableCell className="max-w-xs truncate">{kpi.note}</TableCell>
                <TableCell className="flex gap-2">
                  <Button variant="outline" size="sm" onClick={() => openEditForm(kpi)}>
                    編集
                  </Button>
                  <Button variant="destructive" size="sm" onClick={() => setDeletingKpi(kpi)}>
                    削除
                  </Button>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>

      <KpiForm
        open={formOpen}
        onOpenChange={setFormOpen}
        kpi={editingKpi}
        onSubmit={handleSubmit}
        isSubmitting={createKpi.isPending || updateKpi.isPending}
        error={createKpi.error ?? updateKpi.error}
      />

      <AlertDialog open={!!deletingKpi} onOpenChange={(open) => !open && setDeletingKpi(undefined)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>KPIを削除しますか？</AlertDialogTitle>
            <AlertDialogDescription>
              「{deletingKpi?.name}」を削除します。この操作は元に戻せません。
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
