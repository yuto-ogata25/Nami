import { useQuery } from "@tanstack/react-query";

import { fetchDepartments, fetchFiscalYears, fetchUsers } from "@/lib/api/lookups";

export function useDepartments() {
  return useQuery({
    queryKey: ["departments"],
    queryFn: fetchDepartments,
    select: (response) => response.data,
  });
}

export function useFiscalYears() {
  return useQuery({
    queryKey: ["fiscal-years"],
    queryFn: fetchFiscalYears,
    select: (response) => response.data,
  });
}

export function useCompanyUsers() {
  return useQuery({
    queryKey: ["users"],
    queryFn: fetchUsers,
    select: (response) => response.data,
  });
}
