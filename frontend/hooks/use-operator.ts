import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";

import { fetchCurrentOperator, loginOperator, logoutOperator } from "@/lib/api/auth";
import { ApiError } from "@/lib/api/client";

export function useOperator() {
  return useQuery({
    queryKey: ["operator"],
    queryFn: fetchCurrentOperator,
    retry: false,
    select: (response) => response.data,
    throwOnError: (error) => !(error instanceof ApiError && error.status === 401),
  });
}

export function useLoginOperator() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: loginOperator,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["operator"] });
    },
  });
}

export function useLogoutOperator() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: logoutOperator,
    onSuccess: () => {
      // setQueryData(key, undefined) は更新なしと解釈され無視されるため、
      // removeQueries でキャッシュ自体を削除してログイン状態を確実にクリアする。
      queryClient.removeQueries({ queryKey: ["operator"] });
    },
  });
}
